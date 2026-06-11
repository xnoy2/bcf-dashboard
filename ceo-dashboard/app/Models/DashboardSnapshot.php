<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardSnapshot extends Model
{
    protected $fillable = ['key', 'payload', 'generated_at'];

    protected $casts = [
        'payload'      => 'array',
        'generated_at' => 'datetime',
    ];
}
