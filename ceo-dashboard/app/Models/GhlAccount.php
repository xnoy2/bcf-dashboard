<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhlAccount extends Model
{
    protected $fillable = ['slug', 'name', 'api_key', 'location_id', 'is_active', 'position'];

    protected $casts = [
        'api_key'   => 'encrypted', // stored encrypted, decrypted on read
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * All active DB accounts shaped like config('integrations.accounts') entries,
     * keyed by slug — ready to merge into the static config.
     */
    public static function asConfig(): array
    {
        $out = [];
        foreach (static::active()->orderBy('position')->orderBy('name')->get() as $acc) {
            $out[$acc->slug] = [
                'name' => $acc->name,
                'ghl'  => ['api_key' => $acc->api_key, 'location_id' => $acc->location_id],
            ];
        }

        return $out;
    }
}
