<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardList;
use App\Support\CardPresenter;
use Illuminate\Http\Request;

class BoardListController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, Board $board)
    {
        $this->boardWorkspace($board);

        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);

        $list = $board->lists()->create([
            'name'     => $data['name'],
            'position' => (int) $board->lists()->max('position') + 1,
        ]);

        return response()->json(['ok' => true, 'list' => ['id' => $list->id, 'name' => $list->name]]);
    }

    public function update(Request $request, BoardList $list)
    {
        $this->listWorkspace($list);

        $list->update($request->validate(['name' => ['required', 'string', 'max:255']]));

        return response()->json(['ok' => true]);
    }

    public function destroy(BoardList $list)
    {
        $this->listWorkspace($list);
        \App\Models\CardAttachment::purgeForCards($list->cards()->pluck('id'));
        $list->delete();

        return response()->json(['ok' => true]);
    }

    /** Duplicate a list with its cards (title, description, due date, labels, checklists). */
    public function duplicate(Request $request, BoardList $list)
    {
        $this->listWorkspace($list);
        $board = $list->board;

        // Make room directly after the original list.
        $board->lists()->where('position', '>', $list->position)->increment('position');

        $copy = $board->lists()->create([
            'name'     => $list->name . ' (copy)',
            'position' => $list->position + 1,
        ]);

        $list->load(['cards.labels', 'cards.checklists.items']);
        foreach ($list->cards()->orderBy('position')->get() as $card) {
            $newCard = $copy->cards()->create([
                'title'       => $card->title,
                'description' => $card->description,
                'position'    => $card->position,
                'due_date'    => $card->due_date,
                'created_by'  => $request->user()->id,
            ]);
            $newCard->labels()->sync($card->labels->pluck('id'));
            foreach ($card->checklists as $checklist) {
                $newChecklist = $newCard->checklists()->create([
                    'title' => $checklist->title, 'position' => $checklist->position,
                ]);
                foreach ($checklist->items as $item) {
                    $newChecklist->items()->create([
                        'content' => $item->content, 'is_done' => $item->is_done, 'position' => $item->position,
                    ]);
                }
            }
        }

        $cards = $copy->cards()->orderBy('position')
            ->withCount(['comments', 'attachments'])
            ->with(['labels', 'members', 'checklists.items'])
            ->get()
            ->map(fn ($c) => CardPresenter::summary($c))
            ->values();

        return response()->json([
            'ok'   => true,
            'list' => ['id' => $copy->id, 'name' => $copy->name, 'cards' => $cards],
        ]);
    }

    public function reorder(Request $request, Board $board)
    {
        $this->boardWorkspace($board);

        $order = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ])['order'];

        foreach (array_values($order) as $position => $id) {
            // Scope to this board so a forged id can't move another board's list.
            $board->lists()->whereKey($id)->update(['position' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
