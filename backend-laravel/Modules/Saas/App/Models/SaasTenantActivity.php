<?php

namespace Modules\Saas\App\Models;

use App\Models\BaseModel;

class SaasTenantActivity extends BaseModel
{
    public const UPDATED_AT = null;

    protected $connection;

    protected $table = 'saas_tenant_activity';

    protected $fillable = [
        'tenant_id',
        'actor_id',
        'actor_email',
        'event_type',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.central_connection', config('database.default'));
    }
}
