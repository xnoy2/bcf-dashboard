<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    protected $fillable = ['name', 'color', 'owner_id'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class)->orderBy('position');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** True when the user owns or is a member of this workspace. */
    public function hasMember(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->owner_id === $user->id
            || $this->members()->whereKey($user->id)->exists();
    }

    /** Workspaces the user owns or belongs to. */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('owner_id', $user->id)
            ->orWhereHas('members', fn ($m) => $m->whereKey($user->id)));
    }
}
