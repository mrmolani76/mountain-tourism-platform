<?php

namespace Modules\Hotel\App\Data;

use Modules\Hotel\App\Models\Reservation;
use Spatie\LaravelData\Optional;

class UpdateReservationData extends HotelData
{
    /**
     * @param  array<int, array<string, mixed>>|Optional  $allocations
     */
    public function __construct(
        public string|Optional $guest_name = new Optional(),
        public string|null|Optional $guest_email = new Optional(),
        public string|null|Optional $guest_phone = new Optional(),
        public string|Optional $check_in_on = new Optional(),
        public string|Optional $check_out_on = new Optional(),
        public int|Optional $adults = new Optional(),
        public int|Optional $children = new Optional(),
        public int|null|Optional $board_plan_id = new Optional(),
        public int|Optional $deposit_cents = new Optional(),
        public string|null|Optional $arrival_mode = new Optional(),
        public string|null|Optional $arrival_time = new Optional(),
        public string|null|Optional $arrival_station = new Optional(),
        public string|Optional $status = new Optional(),
        public string|null|Optional $notes = new Optional(),
        public array|Optional $allocations = new Optional(),
    ) {}

    public static function rules(): array
    {
        return [
            'guest_name' => ['sometimes', 'string', 'max:160'],
            'guest_email' => ['nullable', 'email', 'max:160'],
            'guest_phone' => ['nullable', 'string', 'max:40'],
            'check_in_on' => ['sometimes', 'date'],
            'check_out_on' => ['sometimes', 'date', 'after:check_in_on'],
            'adults' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'children' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'board_plan_id' => ['nullable', 'integer', self::tenantExists('hotel_board_plan')],
            'deposit_cents' => ['sometimes', 'integer', 'min:0'],
            'arrival_mode' => ['nullable', 'string', 'in:'.implode(',', Reservation::ARRIVAL_MODES)],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'arrival_station' => ['nullable', 'string', 'max:160'],
            'status' => ['sometimes', 'string', 'in:'.Reservation::STATUS_PENDING.','.Reservation::STATUS_CONFIRMED],
            'notes' => ['nullable', 'string'],
            'allocations' => ['sometimes', 'array', 'min:1', 'max:20'],
            'allocations.*.accommodation_type' => ['required', 'string', 'in:room,bed'],
            'allocations.*.room_id' => ['required_if:allocations.*.accommodation_type,room', 'integer', self::tenantExists('hotel_room')],
            'allocations.*.hut_bed_id' => ['required_if:allocations.*.accommodation_type,bed', 'integer', self::tenantExists('hotel_hut_bed')],
        ];
    }

    /**
     * Only keys present on the request (Spatie Optional omitted).
     * Keeps explicit nulls so board_plan_id can be cleared.
     *
     * @return array<string, mixed>
     */
    public function toFillArray(): array
    {
        return $this->toArray();
    }
}
