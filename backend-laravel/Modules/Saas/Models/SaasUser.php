<?php

namespace Modules\Saas\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Central super-admin (Inventory SaasUser + central guard).
 */
class SaasUser extends Authenticatable
{
    public const UPDATED_AT = null;

    protected $connection;

    protected $table = 'saas_user';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.central_connection', config('database.default'));
    }

    protected $fillable = ['email', 'password', 'is_active'];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'password' => 'hashed',
    ];
}
