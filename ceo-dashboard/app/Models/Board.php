<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Board extends Model
{
    protected $fillable = ['workspace_id', 'name', 'color', 'position', 'created_by'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lists(): HasMany
    {
        return $this->hasMany(BoardList::class)->orderBy('position');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    /** All cards on the board, through its lists — handy for counts. */
    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, BoardList::class);
    }
}
