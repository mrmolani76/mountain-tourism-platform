<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Central registry domain — host → tenant mapping (audit §3: saas_domain).
 * Resolution source of truth for DB-per-tenant routing.
 */
class Domain extends Model
{
    use HasTimestamps;

    protected $table = 'saas_domain';

    protected $fillable = ['tenant_id', 'hostname', 'is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
