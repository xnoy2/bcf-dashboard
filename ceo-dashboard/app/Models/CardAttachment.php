<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardAttachment extends Model
{
    protected $fillable = ['card_id', 'path', 'original_name', 'mime', 'size'];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
