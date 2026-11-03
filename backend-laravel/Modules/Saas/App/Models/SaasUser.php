<?php

namespace Modules\Saas\App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Modules\Saas\App\Enums\SaasRole;

/**
 * Central super-admin (Inventory SaasUser + central guard).
 * PDF §3 Super admin — create complexes / owners, activate or deactivate.
 */
class SaasUser extends Authenticatable
{
    public const UPDATED_AT = null;

    /** MVP: every saas_user is Super Admin (SaasRole::SUPER_ADMIN). */
    public function roleCode(): string
    {
        return SaasRole::SUPER_ADMIN;
    }

    public function roleDisplay(): string
    {
        return SaasRole::LABELS[SaasRole::SUPER_ADMIN];
    }

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
