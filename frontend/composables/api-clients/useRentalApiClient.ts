/**
 * Module E — Equipment rental API client.
 */

export type EquipmentType = {
  id: number
  name: string
  description: string | null
  is_active: boolean
}

export type EquipmentItem = {
  id: number
  equipment_type_id: number
  item_code: string
  size: string | null
  condition: string
  is_active: boolean
}

export type RentalRow = {
  id: number
  reference: string
  customer_type: string
  stay_id: number | null
  folio_id: number | null
  guest_name: string
  due_on: string | null
  status: string
  deposit_cents: number
}

export const useRentalApiClient = () => {
  const { get, post, patch } = useApiClient()

  const listTypes = () =>
    get<{ results: EquipmentType[] }>('rental/equipment-types')

  const createType = (body: { name: string; description?: string; is_active?: boolean }) =>
    post<EquipmentType>('rental/equipment-types', body)

  const updateType = (id: number, body: Record<string, unknown>) =>
    patch<EquipmentType>(`rental/equipment-types/${id}`, body)

  const listItems = (params: Record<string, unknown> = {}) =>
    get<{ results: EquipmentItem[] }>('rental/equipment-items', params)

  const createItem = (body: Record<string, unknown>) =>
    post<EquipmentItem>('rental/equipment-items', body)

  const updateItem = (id: number, body: Record<string, unknown>) =>
    patch<EquipmentItem>(`rental/equipment-items/${id}`, body)

  const listSeasonPrices = (params: Record<string, unknown> = {}) =>
    get<{ results: Array<Record<string, any>> }>('rental/season-prices', params)

  const createSeasonPrice = (body: Record<string, unknown>) =>
    post<Record<string, any>>('rental/season-prices', body)

  const currentlyOut = (params: Record<string, unknown> = {}) =>
    get<{ results: Array<Record<string, any>> }>('rental/out', params)

  const listRentals = (params: Record<string, unknown> = {}) =>
    get<{ results: RentalRow[] }>('rental/rentals', params)

  const rentOut = (body: Record<string, unknown>) =>
    post<RentalRow & { items?: Array<Record<string, any>> }>('rental/rentals', body)

  const showRental = (id: number) =>
    get<{ rental: RentalRow; items: Array<Record<string, any>> }>(`rental/rentals/${id}`)

  const returnItem = (rentalItemId: number, body: {
    returned_at?: string
    condition_on_return?: string
    damage_fee_cents?: number
  } = {}) => post<Record<string, any>>(`rental/rental-items/${rentalItemId}/return`, body)

  return {
    listTypes,
    createType,
    updateType,
    listItems,
    createItem,
    updateItem,
    listSeasonPrices,
    createSeasonPrice,
    currentlyOut,
    listRentals,
    rentOut,
    showRental,
    returnItem,
  }
}
