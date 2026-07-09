<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhlLocation extends Model
{
    protected $fillable = ['location_id', 'name', 'address', 'is_active', 'synced_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
