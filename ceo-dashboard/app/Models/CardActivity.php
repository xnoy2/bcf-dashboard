<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardActivity extends Model
{
    public $timestamps = false;

    protected $fillable = ['card_id', 'user_id', 'action', 'meta', 'created_at'];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CardActivity $activity) {
            $activity->created_at ??= now();
        });
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
