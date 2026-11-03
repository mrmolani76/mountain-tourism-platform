<?php

namespace App\Models;

use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

/**
 * Immutable audit trail base — eQMS complere/app/Models/BaseAuditTrail.php (GAP-011 spirit).
 *
 * Mountain Module 1 also has domain `core_audit_event` (append-only). This class is for
 * owen-it per-table `hist_*` trails when modules enable auditing.
 */
abstract class BaseAuditTrail extends Audit
{
    public function getTable(): string
    {
        if (! empty($this->table)) {
            return $this->table;
        }

        return config(
            'audit.drivers.database.table',
            Str::snake(Str::pluralStudly(class_basename($this)))
        );
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \LogicException('Audit trail records are immutable and cannot be updated.');
    }

    public function delete(): ?bool
    {
        throw new \LogicException('Audit trail records are immutable and cannot be deleted.');
    }

    public static function destroy($ids): int
    {
        throw new \LogicException('Audit trail records are immutable and cannot be destroyed.');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function () {
            throw new \LogicException('Audit trail records are immutable and cannot be updated.');
        });

        static::deleting(function () {
            throw new \LogicException('Audit trail records are immutable and cannot be deleted.');
        });
    }
}
