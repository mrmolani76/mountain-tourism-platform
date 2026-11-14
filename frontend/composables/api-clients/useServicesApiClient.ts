/**
 * Module F — Hotel services and activities API client.
 */

export type ServiceRow = {
  id: number
  name: string
  service_type: string | null
  description: string | null
  outlet_id: number | null
  building_id: number | null
  duration_minutes: number
  default_capacity: number
  price_cents: number
  price_mode: string
  outdoor: boolean
  is_active: boolean
}

export type SlotRow = {
  id: number
  service_id: number
  starts_at: string | null
  ends_at: string | null
  capacity: number
  booked_count: number
  remaining_capacity: number
  status: string
}

export type BookingRow = {
  id: number
  slot_id: number
  stay_id: number | null
  folio_id: number | null
  guest_name: string
  participants_count: number
  status: string
  charge_id: number | null
}

export const useServicesApiClient = () => {
  const { get, post, patch } = useApiClient()

  const listCatalog = (params: Record<string, unknown> = {}) =>
    get<{ results: ServiceRow[] }>('services/catalog', params)

  const createService = (body: Record<string, unknown>) =>
    post<ServiceRow>('services/catalog', body)

  const updateService = (id: number, body: Record<string, unknown>) =>
    patch<ServiceRow>(`services/catalog/${id}`, body)

  const listScheduleRules = (params: Record<string, unknown> = {}) =>
    get<{ results: Array<Record<string, any>> }>('services/schedule-rules', params)

  const createScheduleRule = (body: Record<string, unknown>) =>
    post<Record<string, any>>('services/schedule-rules', body)

  const listSlots = (params: Record<string, unknown> = {}) =>
    get<{ results: SlotRow[] }>('services/slots', params)

  const createSlot = (body: Record<string, unknown>) =>
    post<SlotRow>('services/slots', body)

  const listSlotStaff = (slotId: number) =>
    get<{ results: Array<Record<string, any>> }>(`services/slots/${slotId}/staff`)

  const assignSlotStaff = (slotId: number, assignments: Array<{ staff_id: number; role?: string }>) =>
    post<Record<string, any>>(`services/slots/${slotId}/staff`, { assignments })

  const listBookings = (params: Record<string, unknown> = {}) =>
    get<{ results: BookingRow[] }>('services/bookings', params)

  const createBooking = (body: Record<string, unknown>) =>
    post<BookingRow & { participants?: Array<Record<string, any>> }>('services/bookings', body)

  const cancelBooking = (id: number) =>
    post<BookingRow>(`services/bookings/${id}/cancel`, {})

  const openOuting = (slotId: number) =>
    post<Record<string, any>>(`services/slots/${slotId}/outing`, {})

  const markParticipantOut = (outingParticipantId: number) =>
    post<Record<string, any>>(`services/outing-participants/${outingParticipantId}/out`, {})

  const markParticipantBack = (outingParticipantId: number) =>
    post<Record<string, any>>(`services/outing-participants/${outingParticipantId}/back`, {})

  const closeOuting = (outingId: number) =>
    post<Record<string, any>>(`services/outings/${outingId}/close`, {})

  return {
    listCatalog,
    createService,
    updateService,
    listScheduleRules,
    createScheduleRule,
    listSlots,
    createSlot,
    listSlotStaff,
    assignSlotStaff,
    listBookings,
    createBooking,
    cancelBooking,
    openOuting,
    markParticipantOut,
    markParticipantBack,
    closeOuting,
  }
}
