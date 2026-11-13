<?php

namespace Modules\Transport\App\Services;

use App\Support\Http\Envelope;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Hotel\App\Models\Reservation;
use Modules\Transport\App\Models\Driver;
use Modules\Transport\App\Models\Trip;
use Modules\Transport\App\Models\TripPassenger;
use Modules\Transport\App\Models\Vehicle;

/**
 * Trip create with driver/vehicle overlap guards, seat booking, reservation pickup.
 */
class TripService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Trip
    {
        $starts = CarbonImmutable::parse((string) $data['starts_at']);
        $ends = CarbonImmutable::parse((string) $data['ends_at']);
        if ($ends->lessThanOrEqualTo($starts)) {
            Envelope::abort('invalid_window', 'ends_at must be after starts_at.', [], 422);
        }

        $vehicleId = isset($data['vehicle_id']) ? (int) $data['vehicle_id'] : null;
        $driverId = isset($data['driver_id']) ? (int) $data['driver_id'] : null;
        $seats = isset($data['seats']) ? (int) $data['seats'] : null;

        if ($vehicleId !== null) {
            $vehicle = Vehicle::on(config('tenancy.connection_name'))->find($vehicleId);
            if ($vehicle === null || ! $vehicle->getAttribute('is_active')) {
                Envelope::abort('vehicle_unavailable', 'Vehicle not found or inactive.', [], 422);
            }
            $this->assertNoOverlap('vehicle_id', $vehicleId, $starts, $ends);
            if ($seats === null) {
                $seats = (int) $vehicle->getAttribute('seats');
            }
        }

        if ($driverId !== null) {
            $driver = Driver::on(config('tenancy.connection_name'))->find($driverId);
            if ($driver === null || ! $driver->getAttribute('is_active')) {
                Envelope::abort('driver_unavailable', 'Driver not found or inactive.', [], 422);
            }
            $this->assertNoOverlap('driver_id', $driverId, $starts, $ends);
        }

        if ($seats === null || $seats < 1) {
            Envelope::abort('seats_required', 'seats must be at least 1.', [], 422);
        }

        return Trip::on(config('tenancy.connection_name'))->create([
            'route_id' => $data['route_id'] ?? null,
            'vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'trip_type' => $data['trip_type'] ?? Trip::TYPE_SHUTTLE,
            'starts_at' => $starts,
            'ends_at' => $ends,
            'seats' => $seats,
            'status' => Trip::STATUS_SCHEDULED,
            'reservation_id' => $data['reservation_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Create a pickup trip from reservation arrival fields (PDF Module G).
     *
     * @param  array<string, mixed>  $data
     */
    public function pickupFromReservation(array $data): Trip
    {
        $reservation = Reservation::on(config('tenancy.connection_name'))
            ->find((int) $data['reservation_id']);
        if ($reservation === null) {
            Envelope::abort('reservation_missing', 'Reservation not found.', [], 404);
        }

        $mode = $reservation->getAttribute('arrival_mode');
        if ($mode === null || $mode === 'car') {
            Envelope::abort('no_external_arrival', 'Reservation has no external arrival to pick up.', [], 422);
        }

        $checkIn = CarbonImmutable::parse((string) $reservation->getAttribute('check_in_on'));
        $arrivalTime = (string) ($reservation->getAttribute('arrival_time') ?? '12:00:00');
        $starts = CarbonImmutable::parse($checkIn->toDateString().' '.$arrivalTime);
        $ends = $starts->addHour();

        $seats = max(1, $reservation->guestCount());
        $notes = sprintf(
            'Pickup for reservation %s (%s at %s)',
            $reservation->getAttribute('reference'),
            $mode,
            $reservation->getAttribute('arrival_station') ?? 'unspecified'
        );

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($data, $reservation, $starts, $ends, $seats, $notes) {
            $trip = $this->create([
                'route_id' => $data['route_id'] ?? null,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'driver_id' => $data['driver_id'] ?? null,
                'trip_type' => Trip::TYPE_PICKUP,
                'starts_at' => $starts->toIso8601String(),
                'ends_at' => $ends->toIso8601String(),
                'seats' => $seats,
                'reservation_id' => $reservation->getKey(),
                'notes' => $data['notes'] ?? $notes,
            ]);

            TripPassenger::on(config('tenancy.connection_name'))->create([
                'trip_id' => $trip->getKey(),
                'reservation_id' => $reservation->getKey(),
                'guest_name' => $reservation->getAttribute('guest_name'),
                'seats' => $seats,
                'status' => TripPassenger::STATUS_BOOKED,
            ]);

            return $trip->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function bookPassenger(Trip $trip, array $data): TripPassenger
    {
        if (! in_array($trip->getAttribute('status'), Trip::ACTIVE_STATUSES, true)) {
            Envelope::abort('trip_not_bookable', 'Trip is not open for booking.', [], 422);
        }

        $seats = max(1, (int) ($data['seats'] ?? 1));
        if ($seats > $trip->remainingSeats()) {
            Envelope::abort('seats_exceeded', 'Trip seat capacity would be exceeded.', [
                'remaining' => $trip->remainingSeats(),
                'requested' => $seats,
            ], 422);
        }

        return TripPassenger::on(config('tenancy.connection_name'))->create([
            'trip_id' => $trip->getKey(),
            'stay_id' => $data['stay_id'] ?? null,
            'reservation_id' => $data['reservation_id'] ?? null,
            'guest_name' => $data['guest_name'],
            'seats' => $seats,
            'pickup_stop_id' => $data['pickup_stop_id'] ?? null,
            'dropoff_stop_id' => $data['dropoff_stop_id'] ?? null,
            'status' => TripPassenger::STATUS_BOOKED,
        ]);
    }

    public function cancelPassenger(TripPassenger $passenger): TripPassenger
    {
        if ($passenger->getAttribute('status') !== TripPassenger::STATUS_BOOKED) {
            Envelope::abort('not_cancellable', 'Only booked passengers can be cancelled.', [], 422);
        }

        $passenger->setAttribute('status', TripPassenger::STATUS_CANCELLED);
        $passenger->save();

        return $passenger->fresh();
    }

    /**
     * Printable passenger list payload (drivers do not log in).
     *
     * @return array<string, mixed>
     */
    public function passengerList(Trip $trip): array
    {
        $passengers = TripPassenger::on(config('tenancy.connection_name'))
            ->where('trip_id', $trip->getKey())
            ->whereIn('status', [TripPassenger::STATUS_BOOKED, TripPassenger::STATUS_BOARDED])
            ->orderBy('id')
            ->get();

        $driver = null;
        if ($trip->getAttribute('driver_id') !== null) {
            $driver = Driver::on(config('tenancy.connection_name'))->find((int) $trip->getAttribute('driver_id'));
        }
        $vehicle = null;
        if ($trip->getAttribute('vehicle_id') !== null) {
            $vehicle = Vehicle::on(config('tenancy.connection_name'))->find((int) $trip->getAttribute('vehicle_id'));
        }

        return [
            'trip' => $trip->toApiArray(),
            'driver' => $driver?->toApiArray(),
            'vehicle' => $vehicle?->toApiArray(),
            'passenger_count' => $passengers->sum(fn (TripPassenger $p) => (int) $p->getAttribute('seats')),
            'passengers' => $passengers->map(fn (TripPassenger $p) => $p->toApiArray())->values()->all(),
        ];
    }

    private function assertNoOverlap(string $column, int $id, CarbonImmutable $starts, CarbonImmutable $ends, ?int $ignoreTripId = null): void
    {
        $query = Trip::on(config('tenancy.connection_name'))
            ->where($column, $id)
            ->whereIn('status', Trip::ACTIVE_STATUSES)
            ->where('starts_at', '<', $ends)
            ->where('ends_at', '>', $starts);

        if ($ignoreTripId !== null) {
            $query->where('id', '!=', $ignoreTripId);
        }

        if ($query->exists()) {
            $code = $column === 'driver_id' ? 'driver_overlap' : 'vehicle_overlap';
            Envelope::abort($code, ucfirst(str_replace('_id', '', $column)).' already assigned to an overlapping trip.', [
                $column => $id,
            ], 422);
        }
    }
}
