<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    use AuthorizesWorkspace;

    /** Boards landing: workspace rail + the selected workspace's boards grid. */
    public function index(Request $request)
    {
        $user = $request->user();

        $workspaces = Workspace::accessibleBy($user)
            ->withCount('members')
            ->orderBy('name')
            ->get();

        $current = null;
        if ($id = $request->query('workspace')) {
            $current = $workspaces->firstWhere('id', (int) $id);
        }
        $current ??= $workspaces->first();

        if ($current) {
            $current->load(['boards' => fn ($q) => $q->withCount('cards')]);
        }

        return view('boards.index', [
            'workspaces' => $workspaces,
            'current'    => $current,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:9'],
        ]);

        $workspace = Workspace::create([
            'name'     => $data['name'],
            'color'    => $data['color'] ?? '#3B2A4A',
            'owner_id' => $request->user()->id,
        ]);
        // The owner is also a member (so member counts/lists include them).
        $workspace->members()->attach($request->user()->id, ['role' => 'owner']);

        return redirect()->route('workspaces.index', ['workspace' => $workspace->id]);
    }

    public function update(Request $request, Workspace $workspace)
    {
        $this->guardOwner($workspace);

        $workspace->update($request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:9'],
        ]));

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('refreshed', 'Workspace updated.');
    }

    public function destroy(Request $request, Workspace $workspace)
    {
        $this->guardOwner($workspace);

        $listIds = BoardList::whereIn('board_id', $workspace->boards()->pluck('id'))->pluck('id');
        CardAttachment::purgeForCards(Card::whereIn('board_list_id', $listIds)->pluck('id'));
        $workspace->delete();

        return redirect()->route('workspaces.index')->with('refreshed', 'Workspace deleted.');
    }
}
