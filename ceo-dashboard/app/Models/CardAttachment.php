<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CardAttachment extends Model
{
    protected $fillable = ['card_id', 'disk', 'path', 'original_name', 'mime', 'size'];

    protected static function booted(): void
    {
        // Remove the stored file whenever the record is deleted via Eloquent.
        static::deleting(function (CardAttachment $attachment) {
            Storage::disk($attachment->disk ?: 'boards')->delete($attachment->path);
        });
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    /**
     * Delete attachment files for the given card ids (before a DB cascade
     * removes the rows without firing model events). Chunked for large boards.
     */
    public static function purgeForCards(iterable $cardIds): void
    {
        $ids = collect($cardIds)->filter()->values();
        if ($ids->isEmpty()) {
            return;
        }

        static::whereIn('card_id', $ids)->cursor()->each(fn (self $a) => $a->delete());
    }
}
