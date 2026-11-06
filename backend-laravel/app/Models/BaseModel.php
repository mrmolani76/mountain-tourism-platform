<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * Shared Eloquent base — Mountain app/Models/BaseModel.php.
 *
 * - Optional owen-it auditing (config audit.enabled)
 * - created_by / updated_by / deleted_by only when the column exists (Postgres-safe)
 * - Actor from actor_id() (tenant staff session or Auth)
 */
class BaseModel extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \App\Traits\CustomAuditable;

    /** @var array<string, bool> */
    private static array $columnCache = [];

    protected $guarded = [
        'id',
        'updated_at',
        '_token',
        '_method',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * @return list<object>
     */
    public function getTableColumns(): array
    {
        $connection = $this->getConnectionName() ?: config('database.default');
        $table = $this->getTable();

        // Postgres-friendly column introspection.
        return Schema::connection($connection)->getConnection()->select(
            'SELECT column_name AS "Field", data_type AS "Type"
             FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = ?
             ORDER BY ordinal_position',
            [$table]
        );
    }

    public function audits(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        self::setAuditTable($this);

        return $this->morphMany(
            Config::get('audit.implementation', \OwenIt\Auditing\Models\Audit::class),
            'auditable'
        );
    }

    public function hasIndividualAudit(): bool
    {
        return (bool) ($this->individualAudit ?? false);
    }

    public static function setAuditTable($model = null): void
    {
        if (is_null($model)) {
            $name = static::class;
            $model = new $name;
        }

        $auditTable = 'hist';

        if ($model->hasIndividualAudit()) {
            $auditTable .= '_'.$model->getTable();
        }

        config(['audit.drivers.database.table' => $auditTable]);
    }

    protected static function modelHasColumn(Model $model, string $column): bool
    {
        $key = ($model->getConnectionName() ?: 'default').'.'.$model->getTable().'.'.$column;
        if (! array_key_exists($key, self::$columnCache)) {
            try {
                self::$columnCache[$key] = Schema::connection($model->getConnectionName())
                    ->hasColumn($model->getTable(), $column);
            }
            catch (\Throwable) {
                self::$columnCache[$key] = false;
            }
        }

        return self::$columnCache[$key];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($table) {
            if (static::modelHasColumn($table, 'created_by') && empty($table->created_by)) {
                $table->created_by = actor_id();
            }
            if (static::modelHasColumn($table, 'created_at') && empty($table->created_at)) {
                $table->created_at = Carbon::now();
            }
        });

        static::updating(function ($table) {
            if (static::modelHasColumn($table, 'updated_by') && actor_id()) {
                $table->updated_by = actor_id();
            }
        });

        static::saving(function ($table) {
            if (static::modelHasColumn($table, 'updated_by') && actor_id()) {
                $table->updated_by = actor_id();
            }
        });

        static::deleting(function ($table) {
            if (static::modelHasColumn($table, 'deleted_by')) {
                if (empty($table->deleted_by)) {
                    $table->deleted_by = actor_id();
                }
                $table->save();
            }
        });

        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restoring(function ($table) {
                if (static::modelHasColumn($table, 'deleted_by') && ! empty($table->deleted_by)) {
                    $table->deleted_by = null;
                    $table->save();
                }
            });
        }
    }
}
