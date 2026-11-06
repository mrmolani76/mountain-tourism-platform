/**
 * Hotel module API client (`Modules/Hotel` routes: `api/hotel/*`).
 *
 * Laravel returns either the DRF-style envelope `{ success, data, error, meta }`
 * (list + availability + quote) or a single serialized resource object.
 * Errors are already surfaced as toasts by `API` (`composables/core/useApi`),
 * so helpers return `null` / empty data on failure.
 */

interface Envelope<T> {
  success?: boolean
  data?: T
  error?: unknown
  meta?: Partial<HotelListMeta>
}

const emptyMeta = (): HotelListMeta => ({
  page: 1,
  page_size: 20,
  total_pages: 1,
  total_count: 0,
  has_next: false,
  has_previous: false,
})

const cleanParams = (params: Record<string, unknown>) => {
  const query: Record<string, string> = {}

  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null || value === '')
      return

    query[key] = String(value)
  })

  return query
}

export const useHotelApiClient = () => {
  const { get, post, patch, del } = useApiClient()

  const loading = ref(false)

  const list = async <T,>(endpoint: string, params: Record<string, unknown> = {}): Promise<HotelListPayload<T>> => {
    loading.value = true

    const response = await get<Envelope<Array<T>>>(endpoint, cleanParams(params))

    loading.value = false

    if (!response?.success) {
      return {
        data: [],
        meta: emptyMeta(),
      }
    }

    return {
      data: Array.isArray(response.data) ? response.data : [],
      meta: { ...emptyMeta(), ...(response.meta ?? {}) },
    }
  }

  const show = async <T,>(endpoint: string, id: number): Promise<T | null> => {
    loading.value = true

    const response = await get<T>(`${endpoint}/${id}`)

    loading.value = false

    return response ?? null
  }

  const create = async <T,>(endpoint: string, body: Record<string, unknown>): Promise<T | null> => {
    loading.value = true

    const response = await post<T>(endpoint, body)

    loading.value = false

    return response ?? null
  }

  const update = async <T,>(endpoint: string, id: number, body: Record<string, unknown>): Promise<T | null> => {
    loading.value = true

    const response = await patch<T>(`${endpoint}/${id}`, body)

    loading.value = false

    return response ?? null
  }

  const remove = async (endpoint: string, id: number): Promise<boolean> => {
    loading.value = true

    const response = await del<unknown>(`${endpoint}/${id}`)

    loading.value = false

    // 204 No Content resolves to null; a failed call is toast-handled and also null,
    // so callers refresh the list and see whether the row is gone.
    return response !== undefined
  }

  // ---- Inventory -----------------------------------------------------------

  const listRoomTypes = (params: Record<string, unknown> = {}) => list<RoomType>('hotel/room-types', params)

  const listRooms = (params: Record<string, unknown> = {}) => list<Room>('hotel/rooms', params)

  const listDormitories = (params: Record<string, unknown> = {}) => list<HutDormitory>('hotel/hut-dormitories', params)

  const listBeds = (params: Record<string, unknown> = {}) => list<HutBed>('hotel/hut-beds', params)

  const listSeasonPrices = (params: Record<string, unknown> = {}) => list<RoomSeasonPrice>('hotel/room-season-prices', params)

  const listBoardPlans = (params: Record<string, unknown> = {}) => list<BoardPlan>('hotel/board-plans', params)

  // ---- Front desk ----------------------------------------------------------

  const listReservations = (params: Record<string, unknown> = {}) => list<Reservation>('hotel/reservations', params)

  const showReservation = (id: number) => show<Reservation>('hotel/reservations', id)

  const createReservation = (body: Record<string, unknown>) => create<Reservation>('hotel/reservations', body)

  const updateReservation = (id: number, body: Record<string, unknown>) => update<Reservation>('hotel/reservations', id, body)

  const cancelReservation = (id: number, reason: string) => post<Reservation>(`hotel/reservations/${id}/cancel`, { reason })

  const checkIn = (id: number) => post<{ stay: Stay, folio: { id: number } | null }>(`hotel/reservations/${id}/check-in`, {})

  const listStays = (params: Record<string, unknown> = {}) => list<Stay>('hotel/stays', params)

  const showStay = (id: number) => show<Record<string, unknown>>('hotel/stays', id)

  const stayFolio = (id: number) => get<{
    folio: Record<string, unknown>
    charges: Array<Record<string, unknown>>
    payments: Array<Record<string, unknown>>
    balance_cents: number
  }>(`hotel/stays/${id}/folio`)

  const checkOut = (id: number) => post<Stay>(`hotel/stays/${id}/check-out`, {})

  // ---- Reads ---------------------------------------------------------------

  const availability = async (params: { check_in_on: string, check_out_on: string, building_id?: number | null }) => {
    const response = await get<Envelope<{ rooms: Array<AvailabilityRoom>, beds: Array<AvailabilityBed> }>>(
      'hotel/availability',
      cleanParams(params),
    )

    return response?.success
      ? response.data ?? { rooms: [], beds: [] }
      : { rooms: [], beds: [] }
  }

  const quote = async (params: Record<string, unknown>) => {
    const response = await get<Envelope<Record<string, unknown>>>('hotel/quote', cleanParams(params))

    return response?.success ? response.data ?? null : null
  }

  // ---- Nightly posting -----------------------------------------------------

  const nightlyPostings = (params: Record<string, unknown> = {}) => list<Record<string, unknown>>('hotel/nightly-posting', params)

  const runNightlyPosting = (postedOn?: string) => post<Record<string, unknown>>('hotel/nightly-posting/run', postedOn ? { posted_on: postedOn } : {})

  // ---- Shared reference data (Module 1 Core) -------------------------------

  const listBuildingOptions = async (): Promise<Array<HotelOption>> => {
    const response = await get<Envelope<Array<{ id: number, name: string }>>>('buildings', { page_size: 100 })

    if (!response?.success || !Array.isArray(response.data))
      return []

    return response.data.map(building => ({ value: building.id, title: building.name }))
  }

  const listSeasonOptions = async (): Promise<Array<HotelOption>> => {
    const response = await get<Envelope<Array<{ id: number, kind: string, starts_on: string, ends_on: string }>>>('seasons', { page_size: 100 })

    if (!response?.success || !Array.isArray(response.data))
      return []

    return response.data.map((season) => {
      const label = season.kind ? season.kind.charAt(0).toUpperCase() + season.kind.slice(1) : `Season ${season.id}`

      return {
        value: season.id,
        title: `${label} (${season.starts_on ?? '?'} to ${season.ends_on ?? '?'})`,
      }
    })
  }

  const toOptions = <T extends { id: number }>(rows: Array<T>, label: (row: T) => string): Array<HotelOption> =>
    rows.map(row => ({ value: row.id, title: label(row) }))

  return {
    loading,
    list,
    show,
    create,
    update,
    remove,
    toOptions,

    listRoomTypes,
    listRooms,
    listDormitories,
    listBeds,
    listSeasonPrices,
    listBoardPlans,

    listReservations,
    showReservation,
    createReservation,
    updateReservation,
    cancelReservation,
    checkIn,

    listStays,
    showStay,
    stayFolio,
    checkOut,

    availability,
    quote,

    nightlyPostings,
    runNightlyPosting,

    listBuildingOptions,
    listSeasonOptions,
  }
}
