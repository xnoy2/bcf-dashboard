<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Support\CardPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;

class CardAttachmentController extends Controller
{
    use AuthorizesWorkspace;

    private function maxBytes(): int
    {
        return (int) config('integrations.attachments.max_mb', 500) * 1024 * 1024;
    }

    /**
     * R2 direct upload: issue a short-lived presigned PUT URL so the browser
     * uploads straight to R2 (bypassing PHP/Railway request-size limits).
     */
    public function presign(Request $request, Card $card)
    {
        $this->cardWorkspace($card);
        abort_unless(config('integrations.attachments.disk') === 'r2', 422, 'Direct upload is not enabled.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1', 'max:' . $this->maxBytes()],
        ]);

        $ext = strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));
        $key = 'card-' . $card->id . '/' . Str::uuid() . ($ext ? '.' . $ext : '');

        $signed = Storage::disk('r2')->temporaryUploadUrl($key, now()->addMinutes(15));

        return response()->json([
            'ok'      => true,
            'key'     => $key,
            'url'     => $signed['url'],
            'headers' => $signed['headers'] ?? [],
        ]);
    }

    /** Record an attachment after the browser finished uploading it to R2. */
    public function record(Request $request, Card $card)
    {
        $this->cardWorkspace($card);
        abort_unless(config('integrations.attachments.disk') === 'r2', 422, 'Direct upload is not enabled.');

        $data = $request->validate([
            'key'  => ['required', 'string', 'max:1024'],
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:0', 'max:' . $this->maxBytes()],
            'mime' => ['nullable', 'string', 'max:255'],
        ]);

        // Key must live under this card's prefix, and the object must exist.
        abort_unless(Str::startsWith($data['key'], 'card-' . $card->id . '/'), 422, 'Invalid upload key.');
        abort_unless(Storage::disk('r2')->exists($data['key']), 422, 'Upload not found on storage.');

        // Trust the actual object size on R2, not the client's claim, and
        // re-enforce the cap (the presigned PUT itself doesn't limit size).
        $size = (int) Storage::disk('r2')->size($data['key']);
        if ($size > $this->maxBytes()) {
            Storage::disk('r2')->delete($data['key']);
            abort(422, 'File exceeds the maximum allowed size.');
        }

        $card->attachments()->create([
            'disk'          => 'r2',
            'path'          => $data['key'],
            'original_name' => $data['name'],
            'mime'          => $data['mime'] ?? null,
            'size'          => $size,
        ]);

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }

    /** Local (multipart) upload — fallback when not using R2. */
    public function store(Request $request, Card $card)
    {
        $this->cardWorkspace($card);

        $request->validate([
            'files'   => ['required', 'array', 'max:15'],
            'files.*' => ['file', 'max:15360', // 15 MB per file on the local disk
                'mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,xls,xlsx,txt,csv'],
        ], [
            'files.*.max'   => 'Each file must be 15 MB or smaller.',
            'files.*.mimes' => 'Only images and common document types are allowed.',
        ]);

        foreach ((array) $request->file('files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = $file->store("card-{$card->id}", 'boards');
            $card->attachments()->create([
                'disk'          => 'boards',
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);
        }

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }

    public function show(CardAttachment $attachment)
    {
        $this->attachmentWorkspace($attachment);
        $disk = $attachment->disk ?: 'boards';

        // R2: redirect to a short-lived signed URL (download direct from R2).
        if ($disk === 'r2') {
            $type = $attachment->isImage() ? 'inline' : 'attachment';
            $url = Storage::disk('r2')->temporaryUrl($attachment->path, now()->addMinutes(15), [
                'ResponseContentDisposition' => $type . '; filename="' . addslashes($attachment->original_name) . '"',
            ]);

            return redirect()->away($url);
        }

        // Local disk: stream through the app (images inline, else download).
        abort_unless(Storage::disk('boards')->exists($attachment->path), 404);

        if ($attachment->isImage()) {
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $attachment->original_name,
                'image'
            );

            return Storage::disk('boards')->response($attachment->path, $attachment->original_name, [
                'Content-Disposition' => $disposition,
            ]);
        }

        return Storage::disk('boards')->download($attachment->path, $attachment->original_name);
    }

    public function destroy(CardAttachment $attachment)
    {
        $this->attachmentWorkspace($attachment);

        $card = $attachment->card;
        $attachment->delete(); // model event removes the stored file

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }
}
