/**
 * Module C — Guest folio API client.
 * Money is append-only: post charge/payment, void with reason. Never edit/delete.
 */

export type FolioRow = {
  id: number
  reference: string | null
  status: string
  currency: string
  balance_cents?: number
  stay_id?: number | null
  opened_at?: string | null
  closed_at?: string | null
}

export type FolioDetail = {
  folio: FolioRow
  charges: Array<Record<string, any>>
  payments: Array<Record<string, any>>
  voids: Array<Record<string, any>>
  balance_cents: number
}

export type ChargeToRoomMatch = {
  stay_id: number
  guest_name?: string
  room_number?: string | null
  folio_id?: number | null
  reservation_reference?: string | null
  [key: string]: unknown
}

export const useFolioApiClient = () => {
  const { get, post } = useApiClient()

  const list = (params: Record<string, unknown> = {}) =>
    get<{ results: FolioRow[] }>('folios', params)

  const show = (id: number) =>
    get<FolioDetail>(`folios/${id}`)

  const invoice = (id: number) =>
    get<Record<string, any>>(`folios/${id}/invoice`)

  const searchChargeToRoom = (params: { room_number?: string; guest?: string }) =>
    get<{ count: number; results: ChargeToRoomMatch[] }>('folios/charge-to-room', params)

  const chargeToRoom = (body: {
    stay_id: number
    description: string
    amount_cents: number
    tax_cents?: number
    outlet_id?: number | null
    source_type?: string
    source_id?: string
  }) => post<Record<string, any>>('folios/charge-to-room', body)

  const postCharge = (folioId: number, body: {
    description: string
    amount_cents: number
    tax_cents?: number
    outlet_id?: number | null
    source_type?: string
    source_id?: string
  }) => post<Record<string, any>>(`folios/${folioId}/charges`, body)

  const recordPayment = (folioId: number, body: {
    method: 'cash' | 'card'
    amount_cents: number
    reference?: string
  }) => post<Record<string, any>>(`folios/${folioId}/payments`, body)

  const voidEntry = (folioId: number, body: {
    charge_id?: number
    payment_id?: number
    reason: string
    reason_code?: string
  }) => post<Record<string, any>>(`folios/${folioId}/voids`, body)

  return {
    list,
    show,
    invoice,
    searchChargeToRoom,
    chargeToRoom,
    postCharge,
    recordPayment,
    voidEntry,
  }
}
