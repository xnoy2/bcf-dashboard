<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarAttachment extends Model
{
    protected $fillable = ['calendar_entry_id', 'path', 'original_name', 'mime', 'size'];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(CalendarEntry::class, 'calendar_entry_id');
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
