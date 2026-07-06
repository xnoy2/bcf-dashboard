<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Models\Workspace;
use App\Support\CardPresenter;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    use AuthorizesWorkspace;

    public function store(Request $request, Workspace $workspace)
    {
        $this->guardMember($workspace);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:9'],
        ]);

        // New boards start empty — the user adds their own lists.
        $board = $workspace->boards()->create([
            'name'       => $data['name'],
            'color'      => $data['color'] ?? '#2563EB',
            'position'   => (int) $workspace->boards()->max('position') + 1,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('boards.show', $board);
    }

    public function show(Request $request, Board $board)
    {
        $this->boardWorkspace($board);

        $board->load([
            'workspace.members',
            'labels',
            'lists' => fn ($q) => $q->orderBy('position'),
            'lists.cards' => fn ($q) => $q->orderBy('position')
                ->withCount(['comments', 'attachments'])
                ->with(['labels', 'members', 'checklists.items']),
        ]);

        $lists = $board->lists->map(fn ($list) => [
            'id'    => $list->id,
            'name'  => $list->name,
            'cards' => $list->cards->map(fn ($card) => CardPresenter::summary($card))->values(),
        ])->values();

        return view('boards.show', [
            'board' => $board,
            'lists' => $lists,
        ]);
    }

    public function update(Request $request, Board $board)
    {
        $this->boardWorkspace($board);

        $board->update($request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:9'],
        ]));

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('refreshed', 'Board updated.');
    }

    public function destroy(Request $request, Board $board)
    {
        $workspace = $this->boardWorkspace($board);
        CardAttachment::purgeForCards(
            Card::whereIn('board_list_id', $board->lists()->pluck('id'))->pluck('id')
        );
        $board->delete();

        return redirect()->route('workspaces.index', ['workspace' => $workspace->id])
            ->with('refreshed', 'Board deleted.');
    }
}
