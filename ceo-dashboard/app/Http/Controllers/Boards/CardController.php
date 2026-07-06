<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\BoardList;
use App\Models\Card;
use App\Support\CardPresenter;
use Illuminate\Http\Request;

class CardController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, BoardList $list)
    {
        $this->listWorkspace($list);

        $data = $request->validate(['title' => ['required', 'string', 'max:500']]);

        $card = $list->cards()->create([
            'title'      => $data['title'],
            'position'   => (int) $list->cards()->max('position') + 1,
            'created_by' => $request->user()->id,
        ]);

        $card->activities()->create([
            'user_id' => $request->user()->id,
            'action'  => 'created',
            'meta'    => ['list' => $list->name],
        ]);

        return response()->json(['ok' => true, 'card' => CardPresenter::summary($card)]);
    }

    public function show(Card $card)
    {
        $this->cardWorkspace($card);

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card)]);
    }

    public function update(Request $request, Card $card)
    {
        $this->cardWorkspace($card);

        $validated = $request->validate([
            'title'       => ['sometimes', 'string', 'max:500'],
            'description' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'due_date'    => ['sometimes', 'nullable', 'date'],
            'completed'   => ['sometimes', 'boolean'],
        ]);

        $update = array_intersect_key($validated, array_flip(['title', 'description', 'due_date']));

        if ($request->has('completed')) {
            $update['completed_at'] = $request->boolean('completed') ? now() : null;
        }

        $card->update($update);

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }

    public function destroy(Card $card)
    {
        $this->cardWorkspace($card);
        $card->delete();

        return response()->json(['ok' => true]);
    }

    /** Reorder cards within a list; also handles moving a card into this list. */
    public function reorder(Request $request, BoardList $list)
    {
        $this->listWorkspace($list);

        $order = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ])['order'];

        $boardId = $list->board_id;

        foreach (array_values($order) as $position => $cardId) {
            // Only cards already on this board can be reordered/moved here.
            $card = Card::whereKey($cardId)
                ->whereHas('list', fn ($q) => $q->where('board_id', $boardId))
                ->first();

            if (! $card) {
                continue;
            }

            if ($card->board_list_id !== $list->id) {
                $card->activities()->create([
                    'user_id' => $request->user()->id,
                    'action'  => 'moved',
                    'meta'    => ['from' => $card->list->name, 'to' => $list->name],
                ]);
            }

            $card->update(['board_list_id' => $list->id, 'position' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function toggleLabel(Request $request, Card $card)
    {
        $this->cardWorkspace($card);

        $labelId = $request->validate(['label_id' => ['required', 'integer']])['label_id'];

        // Label must belong to this card's board.
        abort_unless(
            $card->list->board->labels()->whereKey($labelId)->exists(),
            422,
            'Label is not on this board.'
        );

        $card->labels()->toggle($labelId);

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }

    public function toggleMember(Request $request, Card $card)
    {
        $workspace = $this->cardWorkspace($card);

        $userId = $request->validate(['user_id' => ['required', 'integer']])['user_id'];

        // Assignee must be a member of the owning workspace.
        abort_unless($workspace->members()->whereKey($userId)->exists(), 422, 'Not a workspace member.');

        $card->members()->toggle($userId);

        return response()->json(['ok' => true, 'card' => CardPresenter::detail($card->fresh())]);
    }
}
