/**
 * Module D — POS API client (catalog, shifts, orders, board check).
 */

export type PosProduct = {
  id: number
  category_id: number | null
  code: string | null
  name: string
  description: string | null
  price_cents: number
  tax_rate_bp: number
  is_active: boolean
}

export type PosOrder = {
  id: number
  outlet_id: number
  shift_id: number | null
  stay_id: number | null
  folio_id: number | null
  table_label: string | null
  status: string
  subtotal_cents: number
  tax_cents: number
  total_cents: number
  payment_method: string | null
}

export type PosShift = {
  id: number
  outlet_id: number
  status: string
  opening_float_cents?: number
  counted_cash_cents?: number | null
  opened_at?: string | null
  closed_at?: string | null
}

export const usePosApiClient = () => {
  const { get, post, patch } = useApiClient()

  const listCategories = () =>
    get<{ results: Array<Record<string, any>> }>('pos/categories')

  const createCategory = (body: { name: string; sort_order?: number; is_active?: boolean }) =>
    post<Record<string, any>>('pos/categories', body)

  const listProducts = (params: Record<string, unknown> = {}) =>
    get<{ results: PosProduct[] }>('pos/products', params)

  const createProduct = (body: Record<string, unknown>) =>
    post<PosProduct>('pos/products', body)

  const updateProduct = (id: number, body: Record<string, unknown>) =>
    patch<PosProduct>(`pos/products/${id}`, body)

  const listShifts = (params: Record<string, unknown> = {}) =>
    get<{ results: PosShift[] }>('pos/shifts', params)

  const openShift = (body: { outlet_id: number; opening_float_cents?: number; notes?: string }) =>
    post<PosShift>('pos/shifts/open', body)

  const closeShift = (id: number, body: { counted_cash_cents: number; notes?: string }) =>
    post<PosShift>(`pos/shifts/${id}/close`, body)

  const listOrders = (params: Record<string, unknown> = {}) =>
    get<{ results: PosOrder[] }>('pos/orders', params)

  const openOrder = (body: { outlet_id: number; stay_id?: number | null; table_label?: string }) =>
    post<PosOrder>('pos/orders', body)

  const showOrder = (id: number) =>
    get<{ order: PosOrder; items: Array<Record<string, any>> }>(`pos/orders/${id}`)

  const addItem = (orderId: number, body: {
    product_id: number
    quantity: number
    note?: string
    is_board_included?: boolean
    meal?: string
    service_date?: string
  }) => post<Record<string, any>>(`pos/orders/${orderId}/items`, body)

  const settleOrder = (orderId: number, payment_method: 'cash' | 'card' | 'room') =>
    post<{ order: PosOrder; kitchen_ticket: unknown; receipt: unknown }>(
      `pos/orders/${orderId}/settle`,
      { payment_method },
    )

  const boardCheck = (params: { stay_id: number; meal: string; service_date?: string }) =>
    get<Record<string, any>>('pos/board-check', params)

  const kitchenPackedLunches = (params: { for_date: string; outlet_id?: number }) =>
    get<{ for_date: string; results: Array<Record<string, any>> }>('pos/packed-lunches/kitchen-list', params)

  const listOutlets = async () => {
    const res = await get<{ results?: Array<Record<string, any>>; data?: Array<Record<string, any>> } | Array<Record<string, any>>>('outlets')
    if (Array.isArray(res))
      return res
    return res?.results ?? res?.data ?? []
  }

  return {
    listCategories,
    createCategory,
    listProducts,
    createProduct,
    updateProduct,
    listShifts,
    openShift,
    closeShift,
    listOrders,
    openOrder,
    showOrder,
    addItem,
    settleOrder,
    boardCheck,
    kitchenPackedLunches,
    listOutlets,
  }
}
