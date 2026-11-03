<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Schema;

/**
 * eQMS complere/app/Models/BasePivot.php — pivot with actor stamps when columns exist.
 */
class BasePivot extends Pivot
{
    /** @var array<string, bool> */
    private static array $columnCache = [];

    protected $guarded = [
        'updated_at',
    ];

    protected static function modelHasColumn(Pivot $model, string $column): bool
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
    }
}
