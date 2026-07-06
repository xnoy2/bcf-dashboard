<?php

namespace App\Http\Controllers\Boards;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Models\CardComment;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Label;
use App\Models\Workspace;

/**
 * Shared access guard for every board endpoint. Access rule: the current user
 * must own or be a member of the workspace that (transitively) owns the
 * resource. Owner-only actions (delete workspace, manage members) use guardOwner.
 *
 * Each resolver returns the resolved Workspace after guarding, so callers can
 * reuse it (e.g. to scope a redirect back to the workspace).
 */
trait AuthorizesWorkspace
{
    protected function guardMember(Workspace $workspace): Workspace
    {
        abort_unless($workspace->hasMember(request()->user()), 403);

        return $workspace;
    }

    protected function guardOwner(Workspace $workspace): Workspace
    {
        abort_unless($workspace->owner_id === request()->user()?->id, 403);

        return $workspace;
    }

    protected function boardWorkspace(Board $board): Workspace
    {
        return $this->guardMember($board->workspace);
    }

    protected function listWorkspace(BoardList $list): Workspace
    {
        return $this->guardMember($list->board->workspace);
    }

    protected function cardWorkspace(Card $card): Workspace
    {
        return $this->guardMember($card->list->board->workspace);
    }

    protected function labelWorkspace(Label $label): Workspace
    {
        return $this->guardMember($label->board->workspace);
    }

    protected function checklistWorkspace(Checklist $checklist): Workspace
    {
        return $this->cardWorkspace($checklist->card);
    }

    protected function checklistItemWorkspace(ChecklistItem $item): Workspace
    {
        return $this->checklistWorkspace($item->checklist);
    }

    protected function commentWorkspace(CardComment $comment): Workspace
    {
        return $this->cardWorkspace($comment->card);
    }

    protected function attachmentWorkspace(CardAttachment $attachment): Workspace
    {
        return $this->cardWorkspace($attachment->card);
    }
}
