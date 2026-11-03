<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Append-only audit trail (DRF AuditEvent parity).
 */
class AuditEvent extends Model
{
    public const UPDATED_AT = null;

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_VOID = 'void';
    public const ACTION_LOGIN = 'login';

    protected $connection = 'tenant';

    protected $table = 'core_audit_event';

    protected $fillable = [
        'actor_staff_id', 'action', 'entity_type', 'entity_id',
        'old_values', 'new_values', 'reason', 'request_id', 'ip',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new RuntimeException('AuditEvent rows are append-only — do not edit.');
        }

        return parent::save($options);
    }

    public function delete(): ?bool
    {
        throw new RuntimeException('AuditEvent rows are append-only — do not delete.');
    }
}
