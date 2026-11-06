<?php

namespace Modules\Hotel\App\Data;

use Modules\Hotel\App\Models\Reservation;

class StoreReservationData extends HotelData
{
    /**
     * @param  array<int, array<string, mixed>>  $allocations
     */
    public function __construct(
        public string $guest_name,
        public string $check_in_on,
        public string $check_out_on,
        public int $adults,
        public array $allocations,
        public ?string $guest_email = null,
        public ?string $guest_phone = null,
        public ?int $children = null,
        public ?int $board_plan_id = null,
        public ?int $deposit_cents = null,
        public ?string $arrival_mode = null,
        public ?string $arrival_time = null,
        public ?string $arrival_station = null,
        public ?string $status = null,
        public ?string $notes = null,
    ) {}

    public static function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:160'],
            'guest_email' => ['nullable', 'email', 'max:160'],
            'guest_phone' => ['nullable', 'string', 'max:40'],
            'check_in_on' => ['required', 'date'],
            'check_out_on' => ['required', 'date', 'after:check_in_on'],
            'adults' => ['required', 'integer', 'min:1', 'max:50'],
            'children' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'board_plan_id' => ['nullable', 'integer', self::tenantExists('hotel_board_plan')],
            'deposit_cents' => ['sometimes', 'integer', 'min:0'],
            'arrival_mode' => ['nullable', 'string', 'in:'.implode(',', Reservation::ARRIVAL_MODES)],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'arrival_station' => ['nullable', 'string', 'max:160'],
            'status' => ['sometimes', 'string', 'in:'.Reservation::STATUS_PENDING.','.Reservation::STATUS_CONFIRMED],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1', 'max:20'],
            'allocations.*.accommodation_type' => ['required', 'string', 'in:room,bed'],
            'allocations.*.room_id' => ['required_if:allocations.*.accommodation_type,room', 'integer', self::tenantExists('hotel_room')],
            'allocations.*.hut_bed_id' => ['required_if:allocations.*.accommodation_type,bed', 'integer', self::tenantExists('hotel_hut_bed')],
        ];
    }
}
