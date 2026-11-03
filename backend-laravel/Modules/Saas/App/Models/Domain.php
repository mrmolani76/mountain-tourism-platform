<?php

namespace Modules\Saas\App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    public const UPDATED_AT = null;

    protected $connection;

    protected $table = 'saas_domain';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.central_connection', config('database.default'));
    }

    /** DRF column is hostname (Inventory uses domain — we keep DRF parity). */
    protected $fillable = ['tenant_id', 'hostname', 'is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
