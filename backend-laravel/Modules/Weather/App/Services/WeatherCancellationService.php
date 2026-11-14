<?php

namespace Modules\Weather\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\FolioVoid;
use Modules\Folio\App\Services\FolioService;
use Modules\Services\App\Models\Booking;
use Modules\Services\App\Models\BookingParticipant;
use Modules\Services\App\Models\Slot;
use Modules\Transport\App\Models\Lift;
use Modules\Transport\App\Models\LiftStatusHistory;
use Modules\Transport\App\Models\Trip;
use Modules\Transport\App\Models\TripPassenger;
use Modules\Weather\App\Models\WeatherCancellation;
use Modules\Weather\App\Models\WeatherWarningTarget;

/**
 * Explicit staff cancel-for-weather. Never auto-invoked from evaluate.
 * Updates target status and voids related folio charges with reason_code=weather.
 */
class WeatherCancellationService
{
    public function __construct(private readonly FolioService $folios)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *   cancellation: WeatherCancellation,
     *   voids: list<array<string, mixed>>,
     *   guests_to_notify: list<array{name: string, target_type: string, target_id: int}>
     * }
     */
    public function cancel(array $data): array
    {
        $conn = config('tenancy.connection_name');
        $targetType = (string) $data['target_type'];
        $targetId = (int) $data['target_id'];
        $reason = (string) $data['reason'];

        return DB::connection($conn)->transaction(function () use ($conn, $data, $targetType, $targetId, $reason) {
            $this->assertTargetExists($targetType, $targetId);

            $cancellation = WeatherCancellation::on($conn)->create([
                'warning_id' => $data['warning_id'] ?? null,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'cancelled_at' => now(),
                'cancelled_by' => $this->actorId(),
                'reason' => $reason,
                'notes' => $data['notes'] ?? null,
            ]);

            $guests = $this->updateTargetStatus($targetType, $targetId, $reason, $data['warning_id'] ?? null);
            $voids = $this->voidRelatedCharges($targetType, $targetId, $reason);

            return [
                'cancellation' => $cancellation->fresh(),
                'voids' => $voids,
                'guests_to_notify' => $guests,
            ];
        });
    }

    private function assertTargetExists(string $type, int $id): void
    {
        $conn = config('tenancy.connection_name');

        $exists = match ($type) {
            WeatherWarningTarget::TYPE_LIFT => Schema::connection($conn)->hasTable('transport_lift')
                && Lift::on($conn)->whereKey($id)->exists(),
            WeatherWarningTarget::TYPE_TRIP => Schema::connection($conn)->hasTable('transport_trip')
                && Trip::on($conn)->whereKey($id)->exists(),
            WeatherWarningTarget::TYPE_SERVICE_SLOT => Schema::connection($conn)->hasTable('service_slot')
                && Slot::on($conn)->whereKey($id)->exists(),
            default => false,
        };

        if (! $exists) {
            Envelope::abort('not_found', 'Cancellation target not found.', [
                'target_type' => $type,
                'target_id' => $id,
            ], 404);
        }
    }

    /**
     * @return list<array{name: string, target_type: string, target_id: int}>
     */
    private function updateTargetStatus(string $type, int $id, string $reason, mixed $warningId): array
    {
        $conn = config('tenancy.connection_name');
        $guests = [];

        if ($type === WeatherWarningTarget::TYPE_LIFT && class_exists(Lift::class)) {
            LiftStatusHistory::on($conn)->create([
                'lift_id' => $id,
                'status' => LiftStatusHistory::STATUS_WEATHER_HOLD,
                'reason' => $reason,
                'changed_by' => $this->actorId(),
            ]);
        }

        if ($type === WeatherWarningTarget::TYPE_TRIP && class_exists(Trip::class)) {
            $trip = Trip::on($conn)->find($id);
            if ($trip !== null) {
                $trip->setAttribute('status', Trip::STATUS_WEATHER_CANCELLED);
                $trip->save();

                TripPassenger::on($conn)
                    ->where('trip_id', $id)
                    ->whereIn('status', [TripPassenger::STATUS_BOOKED, TripPassenger::STATUS_BOARDED])
                    ->get()
                    ->each(function (TripPassenger $p) use (&$guests, $type, $id) {
                        $name = trim((string) $p->getAttribute('guest_name'));
                        if ($name !== '') {
                            $guests[] = [
                                'name' => $name,
                                'target_type' => $type,
                                'target_id' => $id,
                            ];
                        }
                    });
            }
        }

        if ($type === WeatherWarningTarget::TYPE_SERVICE_SLOT && class_exists(Slot::class)) {
            $slot = Slot::on($conn)->find($id);
            if ($slot !== null) {
                $slot->setAttribute('status', Slot::STATUS_WEATHER_HOLD);
                if ($warningId !== null && Schema::connection($conn)->hasColumn('service_slot', 'weather_warning_id')) {
                    $slot->setAttribute('weather_warning_id', (int) $warningId);
                }
                $slot->save();

                if (class_exists(Booking::class)) {
                    Booking::on($conn)
                        ->where('slot_id', $id)
                        ->where('status', Booking::STATUS_BOOKED)
                        ->get()
                        ->each(function (Booking $booking) use (&$guests, $type, $id, $conn) {
                            $name = trim((string) $booking->getAttribute('guest_name'));
                            if ($name !== '') {
                                $guests[] = [
                                    'name' => $name,
                                    'target_type' => $type,
                                    'target_id' => $id,
                                ];
                            }

                            if (class_exists(BookingParticipant::class)) {
                                BookingParticipant::on($conn)
                                    ->where('booking_id', $booking->getKey())
                                    ->pluck('name')
                                    ->each(function ($participantName) use (&$guests, $type, $id) {
                                        $n = trim((string) $participantName);
                                        if ($n !== '') {
                                            $guests[] = [
                                                'name' => $n,
                                                'target_type' => $type,
                                                'target_id' => $id,
                                            ];
                                        }
                                    });
                            }
                        });
                }
            }
        }

        // Dedupe guest names for this target.
        $seen = [];
        $unique = [];
        foreach ($guests as $g) {
            $key = strtolower($g['name']);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $g;
        }

        return $unique;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function voidRelatedCharges(string $type, int $id, string $reason): array
    {
        $conn = config('tenancy.connection_name');
        if (! Schema::connection($conn)->hasTable('folio_charge') || ! class_exists(Charge::class)) {
            return [];
        }

        $chargeIds = [];

        if ($type === WeatherWarningTarget::TYPE_TRIP && class_exists(TripPassenger::class)) {
            $chargeIds = array_merge(
                $chargeIds,
                TripPassenger::on($conn)
                    ->where('trip_id', $id)
                    ->whereNotNull('charge_id')
                    ->pluck('charge_id')
                    ->map(fn ($v) => (int) $v)
                    ->all()
            );
            $chargeIds = array_merge(
                $chargeIds,
                Charge::on($conn)
                    ->where('source_type', Charge::SOURCE_TRANSPORT)
                    ->where('source_id', (string) $id)
                    ->where('is_voided', false)
                    ->pluck('id')
                    ->map(fn ($v) => (int) $v)
                    ->all()
            );
        }

        if ($type === WeatherWarningTarget::TYPE_SERVICE_SLOT && class_exists(Booking::class)) {
            $chargeIds = array_merge(
                $chargeIds,
                Booking::on($conn)
                    ->where('slot_id', $id)
                    ->whereNotNull('charge_id')
                    ->pluck('charge_id')
                    ->map(fn ($v) => (int) $v)
                    ->all()
            );
            $chargeIds = array_merge(
                $chargeIds,
                Charge::on($conn)
                    ->where('source_type', Charge::SOURCE_SERVICE)
                    ->where('source_id', (string) $id)
                    ->where('is_voided', false)
                    ->pluck('id')
                    ->map(fn ($v) => (int) $v)
                    ->all()
            );
        }

        // Lift cancel: no automatic pass voids (passes remain valid unless staff voids separately).
        $chargeIds = array_values(array_unique(array_filter($chargeIds)));
        $voids = [];

        foreach ($chargeIds as $chargeId) {
            $charge = Charge::on($conn)->where('is_voided', false)->find($chargeId);
            if ($charge === null) {
                continue;
            }

            $folio = Folio::on($conn)->find($charge->getAttribute('folio_id'));
            if ($folio === null || ! $folio->isOpen()) {
                continue;
            }

            $void = $this->folios->voidEntry($folio, [
                'charge_id' => $chargeId,
                'reason' => $reason,
                'reason_code' => FolioVoid::REASON_WEATHER,
            ]);
            $voids[] = $void->toApiArray();
        }

        return $voids;
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
