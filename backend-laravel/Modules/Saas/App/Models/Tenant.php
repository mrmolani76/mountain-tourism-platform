<?php

namespace Modules\Saas\App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Str;

class Tenant extends BaseModel
{
    protected $connection;

    protected $table = 'saas_tenant';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.central_connection', config('database.default'));
    }

    protected $fillable = ['name', 'slug', 'db_name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (self $tenant): void {
            $tenant->setAttribute('uuid', $tenant->getAttribute('uuid') ?: (string) Str::uuid());
        });
    }

    public function domains()
    {
        return $this->hasMany(Domain::class, 'tenant_id');
    }
}
