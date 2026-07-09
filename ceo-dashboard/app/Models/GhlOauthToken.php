<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhlOauthToken extends Model
{
    protected $fillable = ['company_id', 'access_token', 'refresh_token', 'expires_at', 'scope'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /** The single active agency connection, if any. */
    public static function current(): ?self
    {
        return static::latest('id')->first();
    }

    public function isExpired(): bool
    {
        // Treat as expired a little early to avoid mid-request expiry.
        return ! $this->expires_at || $this->expires_at->subMinutes(2)->isPast();
    }
}
