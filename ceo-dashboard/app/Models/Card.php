<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $fillable = [
        'board_list_id', 'title', 'description', 'position',
        'start_date', 'due_date', 'completed_at', 'created_by',
    ];

    protected $casts = [
        'start_date'   => 'datetime',
        'due_date'     => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(BoardList::class, 'board_list_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'card_label');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'card_member');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CardActivity::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CardAttachment::class);
    }

    /** [done, total] across all checklist items on this card. */
    protected function checklistProgress(): Attribute
    {
        return Attribute::get(function () {
            $items = $this->checklists->flatMap->items;

            return [
                'done'  => $items->where('is_done', true)->count(),
                'total' => $items->count(),
            ];
        });
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && ! $this->completed_at
            && $this->due_date->isPast();
    }
}
