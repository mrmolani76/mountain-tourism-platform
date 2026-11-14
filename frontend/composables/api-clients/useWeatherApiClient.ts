/**
 * Module H — Weather API client.
 */

export type WeatherPoint = {
  id: number
  building_id: number | null
  lift_id: number | null
  name: string
  latitude: number
  longitude: number
  altitude_m: number | null
  is_active: boolean
  created_at?: string | null
  updated_at?: string | null
}

export type WeatherLimit = {
  id: number
  weather_point_id: number
  variable: string
  operator: string
  threshold: number
  severity: string
  is_active: boolean
  created_by?: number | null
  created_at?: string | null
  updated_at?: string | null
}

export type WeatherWarningTarget = {
  id: number
  warning_id: number
  target_type: string
  target_id: number
  acknowledged: boolean
  acknowledged_by?: number | null
  acknowledged_at?: string | null
}

export type WeatherWarning = {
  id: number
  weather_point_id: number
  limit_id: number | null
  forecast_value_id: number | null
  variable: string
  threshold: number
  forecast_value: number
  starts_at: string | null
  ends_at: string | null
  status: string
  raised_at: string | null
  resolved_at: string | null
  resolved_by: number | null
  targets?: WeatherWarningTarget[] | null
}

export type AvalancheDaily = {
  id: number
  valid_on: string
  level: number
  source: string | null
  notes: string | null
  entered_by: number | null
  created_at?: string | null
}

export const WEATHER_VARIABLES = [
  { title: 'Temperature (°C)', value: 'temperature_c' },
  { title: 'Wind (m/s)', value: 'wind_ms' },
  { title: 'Gust (m/s)', value: 'gust_ms' },
  { title: 'Precipitation (mm)', value: 'precipitation_mm' },
  { title: 'Symbol', value: 'symbol' },
] as const

export const WEATHER_OPERATORS = [
  { title: '>', value: 'gt' },
  { title: '≥', value: 'gte' },
  { title: '<', value: 'lt' },
  { title: '≤', value: 'lte' },
] as const

export const WEATHER_SEVERITIES = [
  { title: 'Info', value: 'info' },
  { title: 'Warning', value: 'warning' },
  { title: 'Danger', value: 'danger' },
] as const

export const WEATHER_TARGET_TYPES = [
  { title: 'Lift', value: 'lift' },
  { title: 'Trip', value: 'trip' },
  { title: 'Service slot', value: 'service_slot' },
] as const

export const useWeatherApiClient = () => {
  const { get, post, patch, put } = useApiClient()

  const listPoints = () => get<{ results: WeatherPoint[] }>('weather/points')
  const createPoint = (body: Record<string, unknown>) => post<WeatherPoint>('weather/points', body)
  const updatePoint = (id: number, body: Record<string, unknown>) =>
    patch<WeatherPoint>(`weather/points/${id}`, body)
  const fetchPoint = (id: number) => post<Record<string, any>>(`weather/points/${id}/fetch`, {})
  const evaluatePoint = (id: number) =>
    post<{ created: number; updated: number; warnings: WeatherWarning[] }>(
      `weather/points/${id}/evaluate`,
      {},
    )

  const listLimits = (params: Record<string, unknown> = {}) =>
    get<{ results: WeatherLimit[] }>('weather/limits', params)
  const createLimit = (body: Record<string, unknown>) => post<WeatherLimit>('weather/limits', body)
  const updateLimit = (id: number, body: Record<string, unknown>) =>
    patch<WeatherLimit>(`weather/limits/${id}`, body)

  const listWarnings = (params: Record<string, unknown> = {}) =>
    get<{ results: WeatherWarning[] }>('weather/warnings', params)
  const resolveWarning = (id: number) => post<WeatherWarning>(`weather/warnings/${id}/resolve`, {})

  const cancelForWeather = (body: Record<string, unknown>) =>
    post<{
      cancellation: Record<string, any>
      voids: Array<Record<string, any>>
      guests_to_notify: Array<Record<string, any>>
    }>('weather/cancellations', body)

  const listAvalanche = () => get<{ results: AvalancheDaily[] }>('weather/avalanche')
  const upsertAvalanche = (body: Record<string, unknown>) =>
    put<AvalancheDaily>('weather/avalanche', body)

  return {
    listPoints,
    createPoint,
    updatePoint,
    fetchPoint,
    evaluatePoint,
    listLimits,
    createLimit,
    updateLimit,
    listWarnings,
    resolveWarning,
    cancelForWeather,
    listAvalanche,
    upsertAvalanche,
  }
}
