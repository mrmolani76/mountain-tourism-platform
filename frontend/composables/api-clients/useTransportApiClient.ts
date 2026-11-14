/**
 * Module G — Transport and lifts API client.
 */

export type Vehicle = {
  id: number
  name: string
  plate: string
  vehicle_type: string
  seats: number
  is_active: boolean
}

export type Driver = {
  id: number
  name: string
  phone: string | null
  licence_no: string | null
  is_active: boolean
}

export type TransportRoute = {
  id: number
  name: string
  route_type: string
  geometry: unknown
  is_active: boolean
  stops?: Array<Record<string, any>> | null
}

export type Trip = {
  id: number
  route_id: number | null
  vehicle_id: number | null
  driver_id: number | null
  trip_type: string
  starts_at: string | null
  ends_at: string | null
  seats: number
  remaining_seats: number
  status: string
  reservation_id: number | null
  notes: string | null
}

export type Lift = {
  id: number
  name: string
  lift_type: string
  current_status: string | null
  current_status_reason: string | null
  is_active: boolean
}

export const useTransportApiClient = () => {
  const { get, post, patch } = useApiClient()

  const listVehicles = () => get<{ results: Vehicle[] }>('transport/vehicles')
  const createVehicle = (body: Record<string, unknown>) => post<Vehicle>('transport/vehicles', body)
  const updateVehicle = (id: number, body: Record<string, unknown>) => patch<Vehicle>(`transport/vehicles/${id}`, body)

  const listDrivers = () => get<{ results: Driver[] }>('transport/drivers')
  const createDriver = (body: Record<string, unknown>) => post<Driver>('transport/drivers', body)
  const updateDriver = (id: number, body: Record<string, unknown>) => patch<Driver>(`transport/drivers/${id}`, body)

  const listRoutes = () => get<{ results: TransportRoute[] }>('transport/routes')
  const createRoute = (body: Record<string, unknown>) => post<TransportRoute>('transport/routes', body)
  const updateRoute = (id: number, body: Record<string, unknown>) => patch<TransportRoute>(`transport/routes/${id}`, body)

  const listTrips = (params: Record<string, unknown> = {}) => get<{ results: Trip[] }>('transport/trips', params)
  const createTrip = (body: Record<string, unknown>) => post<Trip>('transport/trips', body)
  const showTrip = (id: number) => get<Trip>(`transport/trips/${id}`)
  const pickupFromReservation = (body: Record<string, unknown>) =>
    post<Trip & { passengers?: Array<Record<string, any>> }>('transport/trips/pickup-from-reservation', body)
  const addPassenger = (tripId: number, body: Record<string, unknown>) =>
    post<Record<string, any>>(`transport/trips/${tripId}/passengers`, body)
  const passengerList = (tripId: number) => get<Record<string, any>>(`transport/trips/${tripId}/passenger-list`)
  const cancelPassenger = (passengerId: number) =>
    post<Record<string, any>>(`transport/passengers/${passengerId}/cancel`, {})

  const listLifts = () => get<{ results: Lift[] }>('transport/lifts')
  const createLift = (body: Record<string, unknown>) => post<Lift>('transport/lifts', body)
  const updateLift = (id: number, body: Record<string, unknown>) => patch<Lift>(`transport/lifts/${id}`, body)
  const changeLiftStatus = (id: number, body: { status: string; reason?: string }) =>
    post<{ lift: Lift; history: Record<string, any> }>(`transport/lifts/${id}/status`, body)
  const liftStatusHistory = (id: number) => get<{ results: Array<Record<string, any>> }>(`transport/lifts/${id}/status-history`)
  const lobbyLifts = () => get<{ results: Array<Record<string, any>> }>('transport/lobby/lifts')

  const listLiftPassProducts = () => get<{ results: Array<Record<string, any>> }>('transport/lift-pass-products')
  const createLiftPassProduct = (body: Record<string, unknown>) =>
    post<Record<string, any>>('transport/lift-pass-products', body)
  const sellLiftPass = (body: Record<string, unknown>) =>
    post<Record<string, any>>('transport/lift-passes', body)

  return {
    listVehicles, createVehicle, updateVehicle,
    listDrivers, createDriver, updateDriver,
    listRoutes, createRoute, updateRoute,
    listTrips, createTrip, showTrip, pickupFromReservation, addPassenger, passengerList, cancelPassenger,
    listLifts, createLift, updateLift, changeLiftStatus, liftStatusHistory, lobbyLifts,
    listLiftPassProducts, createLiftPassProduct, sellLiftPass,
  }
}
