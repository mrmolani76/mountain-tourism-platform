<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

/**
 * Central registry tenant — default (central) connection always, on any host.
 * Mirrors Django modules/saas Tenant (audit §3: saas_tenant).
 */
class Tenant extends Model
{
    use HasTimestamps;

    protected $table = 'saas_tenant';

    protected $fillable = ['name', 'slug', 'db_name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
