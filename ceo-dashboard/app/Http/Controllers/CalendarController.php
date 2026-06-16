<?php

namespace App\Http\Controllers;

use App\Models\CalendarAttachment;
use App\Models\CalendarEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CalendarController extends Controller
{
    private const STATUS_COLORS = [
        'scheduled'   => '#5E7C99',
        'in_progress' => '#CD8B3C',
        'completed'   => '#4E7C59',
        'cancelled'   => '#8A7F8E',
    ];

    /** Calendar page: FullCalendar + a filterable list of entries. */
    public function index(Request $request)
    {
        $q = CalendarEntry::query()->with('attachments');

        if ($s = $request->query('status')) {
            $q->where('status', $s);
        }
        if ($a = $request->query('assigned')) {
            $q->where('assigned_to', 'like', "%$a%");
        }
        if ($term = $request->query('q')) {
            $q->where(fn ($w) => $w->where('client_name', 'like', "%$term%")
                ->orWhere('address', 'like', "%$term%")
                ->orWhere('order_details', 'like', "%$term%"));
        }
        if ($from = $request->query('from')) {
            $q->whereRaw('COALESCE(end_date, start_date) >= ?', [$from]);
        }
        if ($to = $request->query('to')) {
            $q->where('start_date', '<=', $to);
        }

        $entries = $q->orderBy('start_date')->get();

        return view('calendar', [
            'accounts'   => config('integrations.accounts'),
            'account'    => 'all',
            'entries'    => $entries,
            'statuses'   => CalendarEntry::STATUSES,
            'installers' => CalendarEntry::query()->whereNotNull('assigned_to')
                ->distinct()->pluck('assigned_to')->filter()->values(),
            'stats'      => [
                'upcoming'  => CalendarEntry::dueWithin(7)->count(),
                'overdue'   => CalendarEntry::overdue()->count(),
                'scheduled' => CalendarEntry::where('status', 'scheduled')->count(),
                'completed' => CalendarEntry::where('status', 'completed')->count(),
            ],
            'filters'    => $request->only(['status', 'assigned', 'q', 'from', 'to']),
        ]);
    }

    /** JSON feed for FullCalendar (respects its start/end window). */
    public function events(Request $request)
    {
        $entries = CalendarEntry::query()
            ->when($request->query('start'), fn ($q, $start) =>
                $q->whereRaw('COALESCE(end_date, start_date) >= ?', [substr($start, 0, 10)]))
            ->when($request->query('end'), fn ($q, $end) =>
                $q->where('start_date', '<=', substr($end, 0, 10)))
            ->get();

        return response()->json($entries->map(function (CalendarEntry $e) {
            $color = self::STATUS_COLORS[$e->status] ?? '#5E7C99';

            return [
                'id'    => $e->id,
                'title' => ($e->is_birthday ? '🎂 ' : '') . $e->client_name,
                'start' => $e->start_date->toDateString(),
                // FullCalendar treats all-day end as exclusive → +1 day.
                'end'   => $e->end_date ? $e->end_date->copy()->addDay()->toDateString() : null,
                'allDay'        => true,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'extendedProps'   => $this->payload($e),
            ];
        }));
    }

    public function store(Request $request)
    {
        $data = $this->validateEntry($request);
        $this->validateFiles($request);
        $data['created_by'] = optional($request->user())->name ?? 'CEO';

        $entry = CalendarEntry::create($data);
        $this->saveFiles($request, $entry);

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'entry' => $this->payload($entry->fresh('attachments'))])
            : back()->with('refreshed', 'Calendar entry created.');
    }

    public function update(Request $request, CalendarEntry $entry)
    {
        // Lightweight updates (drag-reschedule, status, assign) skip full validation.
        if ($request->has('_action')) {
            $entry->update($request->validate([
                'start_date'  => ['sometimes', 'date'],
                'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
                'status'      => ['sometimes', 'in:' . implode(',', CalendarEntry::STATUSES)],
                'assigned_to' => ['sometimes', 'nullable', 'string', 'max:255'],
            ]));

            return response()->json(['ok' => true, 'entry' => $this->payload($entry->fresh('attachments'))]);
        }

        $entry->update($this->validateEntry($request));
        $this->validateFiles($request);
        $this->saveFiles($request, $entry);

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'entry' => $this->payload($entry->fresh('attachments'))])
            : back()->with('refreshed', 'Calendar entry updated.');
    }

    public function destroy(CalendarEntry $entry)
    {
        foreach ($entry->attachments as $a) {
            Storage::disk('calendar')->delete($a->path);
        }
        $entry->delete();

        return response()->json(['ok' => true]);
    }

    public function attachment(CalendarAttachment $attachment)
    {
        abort_unless(Storage::disk('calendar')->exists($attachment->path), 404);

        // Images render inline (so thumbnails/preview work); other files download.
        if ($attachment->isImage()) {
            $disposition = \Symfony\Component\HttpFoundation\HeaderUtils::makeDisposition(
                \Symfony\Component\HttpFoundation\HeaderUtils::DISPOSITION_INLINE,
                $attachment->original_name,
                'image' // ASCII fallback if the name has non-ASCII chars
            );

            return Storage::disk('calendar')->response($attachment->path, $attachment->original_name, [
                'Content-Disposition' => $disposition,
            ]);
        }

        return Storage::disk('calendar')->download($attachment->path, $attachment->original_name);
    }

    public function destroyAttachment(CalendarAttachment $attachment)
    {
        Storage::disk('calendar')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['ok' => true]);
    }

    // ---------- helpers ----------

    private function validateEntry(Request $request): array
    {
        $data = $request->validate([
            'client_name'   => ['required', 'string', 'max:255'],
            'address'       => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_birthday'   => ['sometimes', 'boolean'],
            'dob'           => ['nullable', 'required_if:is_birthday,1', 'date'],
            'order_details' => ['nullable', 'string', 'max:5000'],
            'status'        => ['required', 'in:' . implode(',', CalendarEntry::STATUSES)],
            'business'      => ['nullable', 'string', 'max:50'],
            'assigned_to'   => ['nullable', 'string', 'max:255'],
            'reminder_days' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);

        $data['is_birthday'] = $request->boolean('is_birthday');
        if (! $data['is_birthday']) {
            $data['dob'] = null;
        }
        $data['reminder_days'] = $data['reminder_days'] ?? 3;

        return $data;
    }

    private function validateFiles(Request $request): void
    {
        $request->validate([
            'files'   => ['nullable', 'array', 'max:15'],
            'files.*' => ['file', 'max:15360', // 15 MB per file
                'mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,xls,xlsx,txt,csv'],
        ], [
            'files.*.max'   => 'Each file must be 15 MB or smaller.',
            'files.*.mimes' => 'Only images and common document types (PDF, Word, Excel, txt, csv) are allowed.',
        ]);
    }

    private function saveFiles(Request $request, CalendarEntry $entry): void
    {
        foreach ((array) $request->file('files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = $file->store("entry-{$entry->id}", 'calendar');
            $entry->attachments()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);
        }
    }

    private function payload(CalendarEntry $e): array
    {
        return [
            'id'            => $e->id,
            'client_name'   => $e->client_name,
            'address'       => $e->address,
            'phone'         => $e->phone,
            'start_date'    => $e->start_date->toDateString(),
            'end_date'      => $e->end_date?->toDateString(),
            'is_birthday'   => $e->is_birthday,
            'dob'           => $e->dob?->toDateString(),
            'order_details' => $e->order_details,
            'status'        => $e->status,
            'business'      => $e->business,
            'assigned_to'   => $e->assigned_to,
            'reminder_days' => $e->reminder_days,
            'overdue'       => $e->isOverdue(),
            'attachments'   => $e->attachments->map(fn ($a) => [
                'id'    => $a->id,
                'name'  => $a->original_name,
                'url'   => route('calendar.attachment', $a),
                'image' => $a->isImage(),
            ])->all(),
        ];
    }
}
