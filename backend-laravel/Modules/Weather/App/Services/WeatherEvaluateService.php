<?php

namespace Modules\Weather\App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Services\App\Models\ServiceDefinition;
use Modules\Services\App\Models\Slot;
use Modules\Transport\App\Models\Lift;
use Modules\Transport\App\Models\Trip;
use Modules\Weather\App\Models\WeatherForecastSnapshot;
use Modules\Weather\App\Models\WeatherForecastValue;
use Modules\Weather\App\Models\WeatherLimit;
use Modules\Weather\App\Models\WeatherPoint;
use Modules\Weather\App\Models\WeatherWarning;
use Modules\Weather\App\Models\WeatherWarningTarget;

/**
 * Compare current forecast values vs active limits; raise warnings + soft-link targets.
 * Never auto-cancels — staff must call the cancellations endpoint.
 */
class WeatherEvaluateService
{
    /**
     * @return array{warnings: list<WeatherWarning>, created: int, updated: int}
     */
    public function evaluate(WeatherPoint $point): array
    {
        $conn = config('tenancy.connection_name');
        $snapshot = WeatherForecastSnapshot::on($conn)
            ->where('weather_point_id', $point->getKey())
            ->where('is_current', true)
            ->first();

        if ($snapshot === null) {
            return ['warnings' => [], 'created' => 0, 'updated' => 0];
        }

        $limits = WeatherLimit::on($conn)
            ->where('weather_point_id', $point->getKey())
            ->where('is_active', true)
            ->get();

        if ($limits->isEmpty()) {
            return ['warnings' => [], 'created' => 0, 'updated' => 0];
        }

        $values = WeatherForecastValue::on($conn)
            ->where('snapshot_id', $snapshot->getKey())
            ->orderBy('valid_at')
            ->get();

        $created = 0;
        $updated = 0;
        $warnings = [];

        return DB::connection($conn)->transaction(function () use (
            $point, $limits, $values, $conn, &$created, &$updated, &$warnings
        ) {
            foreach ($limits as $limit) {
                $breaches = $values
                    ->where('variable', $limit->getAttribute('variable'))
                    ->filter(fn (WeatherForecastValue $v) => $this->breaches(
                        (float) $v->getAttribute('value'),
                        (string) $limit->getAttribute('operator'),
                        (float) $limit->getAttribute('threshold'),
                    ));

                if ($breaches->isEmpty()) {
                    continue;
                }

                /** @var WeatherForecastValue $first */
                $first = $breaches->first();
                /** @var WeatherForecastValue $last */
                $last = $breaches->last();

                $startsAt = Carbon::parse($first->getAttribute('valid_at'));
                $endsAt = Carbon::parse($last->getAttribute('valid_at'))->addHour();

                $existing = WeatherWarning::on($conn)
                    ->where('weather_point_id', $point->getKey())
                    ->where('limit_id', $limit->getKey())
                    ->where('status', WeatherWarning::STATUS_ACTIVE)
                    ->first();

                if ($existing !== null) {
                    $existing->fill([
                        'forecast_value_id' => $first->getKey(),
                        'variable' => $limit->getAttribute('variable'),
                        'threshold' => $limit->getAttribute('threshold'),
                        'forecast_value' => $first->getAttribute('value'),
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                    ]);
                    $existing->save();
                    $warning = $existing->fresh();
                    $updated++;
                } else {
                    $warning = WeatherWarning::on($conn)->create([
                        'weather_point_id' => $point->getKey(),
                        'limit_id' => $limit->getKey(),
                        'forecast_value_id' => $first->getKey(),
                        'variable' => $limit->getAttribute('variable'),
                        'threshold' => $limit->getAttribute('threshold'),
                        'forecast_value' => $first->getAttribute('value'),
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'status' => WeatherWarning::STATUS_ACTIVE,
                        'raised_at' => now(),
                    ]);
                    $created++;
                }

                $this->syncTargets($point, $warning, $startsAt, $endsAt);
                $warnings[] = $warning->load('targets');
            }

            return [
                'warnings' => $warnings,
                'created' => $created,
                'updated' => $updated,
            ];
        });
    }

    private function breaches(float $value, string $operator, float $threshold): bool
    {
        return match ($operator) {
            WeatherLimit::OP_GT => $value > $threshold,
            WeatherLimit::OP_GTE => $value >= $threshold,
            WeatherLimit::OP_LT => $value < $threshold,
            WeatherLimit::OP_LTE => $value <= $threshold,
            default => false,
        };
    }

    private function syncTargets(WeatherPoint $point, WeatherWarning $warning, Carbon $startsAt, Carbon $endsAt): void
    {
        $conn = config('tenancy.connection_name');
        $pairs = [];

        if (Schema::connection($conn)->hasTable('transport_lift') && class_exists(Lift::class)) {
            $pointLiftId = $point->getAttribute('lift_id');

            $liftQuery = Lift::on($conn)->where('is_active', true);
            if ($pointLiftId !== null) {
                $liftQuery->whereKey((int) $pointLiftId);
            }

            $liftQuery->pluck('id')->each(function ($id) use (&$pairs) {
                $pairs[] = [WeatherWarningTarget::TYPE_LIFT, (int) $id];
            });
        }

        if (Schema::connection($conn)->hasTable('transport_trip') && class_exists(Trip::class)) {
            Trip::on($conn)
                ->whereIn('status', Trip::ACTIVE_STATUSES)
                ->where('starts_at', '<=', $endsAt)
                ->where(function ($q) use ($startsAt) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $startsAt);
                })
                ->pluck('id')
                ->each(function ($id) use (&$pairs) {
                    $pairs[] = [WeatherWarningTarget::TYPE_TRIP, (int) $id];
                });
        }

        if (Schema::connection($conn)->hasTable('service_slot') && class_exists(Slot::class)) {
            $slotQuery = Slot::on($conn)
                ->where('status', Slot::STATUS_SCHEDULED)
                ->where('starts_at', '<=', $endsAt)
                ->where('ends_at', '>=', $startsAt);

            if (
                Schema::connection($conn)->hasTable('service_service')
                && Schema::connection($conn)->hasColumn('service_service', 'outdoor')
                && class_exists(ServiceDefinition::class)
            ) {
                $outdoorIds = ServiceDefinition::on($conn)
                    ->where('outdoor', true)
                    ->pluck('id');
                $slotQuery->whereIn('service_id', $outdoorIds);
            }

            $slotQuery->pluck('id')->each(function ($id) use (&$pairs) {
                $pairs[] = [WeatherWarningTarget::TYPE_SERVICE_SLOT, (int) $id];
            });
        }

        foreach ($pairs as [$type, $id]) {
            WeatherWarningTarget::on($conn)->firstOrCreate(
                [
                    'warning_id' => $warning->getKey(),
                    'target_type' => $type,
                    'target_id' => $id,
                ],
                [
                    'acknowledged' => false,
                ]
            );
        }
    }
}
