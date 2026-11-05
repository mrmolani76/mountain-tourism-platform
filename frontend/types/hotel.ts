/** Hotel module (Laravel `Modules/Hotel`) API types. Money is integer minor units. */

export interface HotelListMeta {
  page: number
  page_size: number
  total_pages: number
  total_count: number
  has_next: boolean
  has_previous: boolean
}

export interface HotelListPayload<T> {
  data: Array<T>
  meta: HotelListMeta
}

export interface RoomType {
  id: number
  name: string
  description: string | null
  max_adults: number
  max_children: number
  is_active: boolean
}

export interface Room {
  id: number
  building_id: number
  room_type_id: number
  room_number: string
  name: string | null
  accommodation_type: 'room' | 'chalet' | string
  is_active: boolean
}

export interface HutDormitory {
  id: number
  building_id: number
  room_type_id: number | null
  name: string
  is_active: boolean
}

export interface HutBed {
  id: number
  hut_dormitory_id: number
  bed_number: string
  is_active: boolean
}

export interface RoomSeasonPrice {
  id: number
  room_type_id: number
  season_id: number
  amount_cents: number
}

export interface BoardPlan {
  id: number
  code: string
  name: string
  price_per_person_cents: number
  inclusions: Array<string>
  is_active: boolean
}

export interface ReservationAllocation {
  id?: number
  accommodation_type: 'room' | 'bed'
  room_id: number | null
  hut_bed_id: number | null
  nightly_price_cents?: number
  board_price_per_person_cents?: number
}

export interface Reservation {
  id: number
  reference: string
  guest_name: string
  guest_email: string | null
  guest_phone: string | null
  check_in_on: string
  check_out_on: string
  nights: number
  adults: number
  children: number
  board_plan_id: number | null
  deposit_cents: number
  arrival_mode: string | null
  arrival_time: string | null
  arrival_station: string | null
  status: string
  notes?: string | null
  allocations: Array<ReservationAllocation>
}

export interface Stay {
  id: number
  reservation_id: number | null
  folio_id: number | null
  room_id: number | null
  hut_bed_id: number | null
  checked_in_at: string | null
  checked_out_at: string | null
  status: string
}

export interface HotelOption {
  value: number | string
  title: string
}

export interface AvailabilityRoom {
  id: number
  building_id: number
  room_type_id: number
  room_number: string
  name: string | null
  accommodation_type: string
  available: boolean
}

export interface AvailabilityBed {
  id: number
  hut_dormitory_id: number
  bed_number: string
  available: boolean
}

export type HotelFieldType = 'text' | 'textarea' | 'number' | 'money' | 'checkbox' | 'select' | 'multiselect' | 'date' | 'time' | 'email'

export interface HotelFormField {
  key: string
  label: string
  type?: HotelFieldType
  required?: boolean
  hint?: string
  min?: number
  max?: number
  step?: number
  options?: Array<HotelOption>
  optionsLoader?: () => Promise<Array<HotelOption>>
  col?: 12 | 6 | 4
}

export type HotelColumnKind = 'text' | 'money' | 'bool' | 'status' | 'date' | 'datetime'

export interface HotelColumn {
  key: string
  title: string
  kind?: HotelColumnKind
  align?: 'start' | 'center' | 'end'
}

