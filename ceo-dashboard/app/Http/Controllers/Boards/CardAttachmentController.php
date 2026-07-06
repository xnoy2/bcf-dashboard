<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Support\CardPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;

class CardAttachmentController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, Card $card)
    {
        $this->cardWorkspace($card);

        $request->validate([
            'files'   => ['required', 'array', 'max:15'],
            'files.*' => ['file', 'max:15360', // 15 MB per file
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
        abort_unless(Storage::disk('boards')->exists($attachment->path), 404);

        // Images render inline (preview/thumbnails); other files download.
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
        Storage::disk('boards')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }
}
