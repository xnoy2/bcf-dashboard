<?php

namespace App\Support;

use App\Models\Card;
use App\Models\User;

/**
 * Single source of truth for the JSON shape of a card, used both by the board
 * view (compact tile) and the card modal (full detail). Keeps the frontend and
 * every mutating endpoint returning identical structures.
 */
class CardPresenter
{
    /** Compact card for a list tile. */
    public static function summary(Card $card): array
    {
        $card->loadMissing(['labels', 'members', 'checklists.items']);
        $progress = $card->checklist_progress;

        return [
            'id'          => $card->id,
            'list_id'     => $card->board_list_id,
            'title'       => $card->title,
            'position'    => $card->position,
            'completed'   => (bool) $card->completed_at,
            'has_description' => filled($card->description),
            'due'         => self::due($card),
            'labels'      => $card->labels->map(fn ($l) => [
                'id' => $l->id, 'name' => $l->name, 'color' => $l->color,
            ])->values(),
            'members'     => $card->members->map(fn ($u) => self::person($u))->values(),
            'checklist'   => $progress,
            'counts'      => [
                // Prefer eager-loaded counts (withCount) to avoid N+1 on the board.
                'comments'    => $card->comments_count ?? $card->comments()->count(),
                'attachments' => $card->attachments_count ?? $card->attachments()->count(),
            ],
        ];
    }

    /** Full card for the detail modal. */
    public static function detail(Card $card): array
    {
        $card->loadMissing([
            'labels', 'members', 'checklists.items',
            'comments.user', 'activities.user', 'attachments',
        ]);
        $board = $card->list->board;

        return array_merge(self::summary($card), [
            'description' => $card->description,
            'start'       => $card->start_date?->toDateString(),
            'board_id'    => $board->id,
            'list_name'   => $card->list->name,
            'checklists'  => $card->checklists->map(fn ($cl) => [
                'id'    => $cl->id,
                'title' => $cl->title,
                'items' => $cl->items->map(fn ($it) => [
                    'id' => $it->id, 'content' => $it->content, 'is_done' => $it->is_done,
                ])->values(),
                'progress' => [
                    'done'  => $cl->items->where('is_done', true)->count(),
                    'total' => $cl->items->count(),
                ],
            ])->values(),
            'comments'    => $card->comments->map(fn ($c) => [
                'id'      => $c->id,
                'body'    => $c->body,
                'user'    => self::person($c->user),
                'created' => $c->created_at?->diffForHumans(),
            ])->values(),
            'activities'  => $card->activities->map(fn ($a) => [
                'id'      => $a->id,
                'action'  => $a->action,
                'meta'    => $a->meta,
                'user'    => self::person($a->user),
                'created' => $a->created_at?->diffForHumans(),
            ])->values(),
            'attachments' => $card->attachments->map(fn ($a) => [
                'id'    => $a->id,
                'name'  => $a->original_name,
                'url'   => route('boards.attachments.show', $a),
                'image' => $a->isImage(),
                'size'  => (int) $a->size,
            ])->values(),
            // Pickers: labels are board-scoped; members can be any dashboard user.
            'board_labels'  => $board->labels->map(fn ($l) => [
                'id' => $l->id, 'name' => $l->name, 'color' => $l->color,
            ])->values(),
            'board_members' => User::orderBy('name')->get()->map(fn ($u) => self::person($u))->values(),
        ]);
    }

    private static function due(Card $card): ?array
    {
        if (! $card->due_date) {
            return null;
        }

        return [
            'iso'       => $card->due_date->toDateTimeLocalString(),
            'label'     => $card->due_date->format('M j'),
            'completed' => (bool) $card->completed_at,
            'overdue'   => $card->isOverdue(),
        ];
    }

    private static function person(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'initials' => self::initials($user->name),
        ];
    }

    public static function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $second = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';

        return mb_strtoupper($first . $second) ?: '?';
    }
}
