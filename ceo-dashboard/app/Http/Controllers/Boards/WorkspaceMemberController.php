<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WorkspaceMemberController extends Controller
{
    use AuthorizesWorkspace;

    public function index(Request $request, Workspace $workspace)
    {
        $this->guardMember($workspace);
        $workspace->load('members');

        return view('boards.members', [
            'workspace' => $workspace,
            // Dashboard users not yet in this workspace — candidates to add.
            'candidates' => User::whereNotIn('id', $workspace->members->pluck('id'))
                ->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request, Workspace $workspace)
    {
        $this->guardOwner($workspace);

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'No dashboard user with that email.',
            ]);
        }

        // syncWithoutDetaching keeps existing members and is idempotent.
        $workspace->members()->syncWithoutDetaching([
            $user->id => ['role' => 'member'],
        ]);

        return back()->with('refreshed', "{$user->name} added to the workspace.");
    }

    public function destroy(Request $request, Workspace $workspace, User $user)
    {
        $this->guardOwner($workspace);

        // The owner can't be removed from their own workspace.
        abort_if($user->id === $workspace->owner_id, 422, 'The owner cannot be removed.');

        $workspace->members()->detach($user->id);

        return back()->with('refreshed', 'Member removed.');
    }
}
