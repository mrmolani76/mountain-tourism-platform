/**
 * Module I — Owner dashboard and reports API client.
 */

import { resolveApiOrigin } from '@/utils/tenantHost'

export type ReportRow = {
  key: string
  label: string
  amount_cents: number
}

export type RevenueReport = {
  currency: string
  from_date: string
  to_date: string
  basis: string
  total_cents: number
  rows: ReportRow[]
}

export type WeatherCancellationRow = ReportRow & {
  id: number
  warning_id: number | null
  target_type: string
  target_id: number
  cancelled_at: string | null
  cancelled_by: number | null
  reason: string | null
  notes: string | null
}

export type WeatherCancellationReport = RevenueReport & {
  count: number
  rows: WeatherCancellationRow[]
}

export type TodayDashboard = {
  date: string
  currency: string
  occupancy_by_building: {
    results: Array<{
      building_id: number
      building_name: string
      rooms_total: number
      occupied: number
    }>
  }
  arrivals_today: {
    results: Array<Record<string, unknown>>
  }
  departures_today: {
    results: Array<Record<string, unknown>>
  }
  revenue_by_outlet_today: {
    results: Array<{ outlet_id: number | null, label: string, amount_cents: number }>
    total_cents: number
  }
  rentals_out: {
    results: Array<Record<string, unknown>>
    count: number
  }
  open_folios: {
    count: number
    balance_cents: number | null
  }
  lift_status: {
    results: Array<Record<string, unknown>>
  }
  weather_warnings: {
    results: Array<Record<string, unknown>>
  }
}

export type ReportRangeParams = {
  from_date: string
  to_date: string
}

function readCookie(name: string): string | null {
  if (typeof document === 'undefined')
    return null

  const prefix = `${name}=`
  const match = document.cookie.split('; ').find(row => row.startsWith(prefix))

  return match ? decodeURIComponent(match.slice(prefix.length)) : null
}

async function downloadCsv(path: string, params: ReportRangeParams, filename: string): Promise<void> {
  const origin = resolveApiOrigin()
  const qs = new URLSearchParams(params).toString()
  const headers: Record<string, string> = {}
  const xsrf = readCookie('XSRF-TOKEN')
  if (xsrf)
    headers['X-XSRF-TOKEN'] = xsrf

  const res = await fetch(`${origin}/api/${path}?${qs}`, {
    method: 'GET',
    credentials: 'include',
    headers,
  })

  if (!res.ok) {
    const { $toast } = useNuxtApp()
    $toast.error('CSV download failed.')

    return
  }

  const blob = await res.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}

export const useReportsApiClient = () => {
  const { get } = useApiClient()

  const today = () => get<TodayDashboard>('reports/today')

  const revenueByOutlet = (params: ReportRangeParams) =>
    get<RevenueReport>('reports/revenue/by-outlet', params as Record<string, unknown>)

  const revenueByService = (params: ReportRangeParams) =>
    get<RevenueReport>('reports/revenue/by-service', params as Record<string, unknown>)

  const revenueByPaymentMethod = (params: ReportRangeParams) =>
    get<RevenueReport>('reports/revenue/by-payment-method', params as Record<string, unknown>)

  const weatherCancellations = (params: ReportRangeParams) =>
    get<WeatherCancellationReport>('reports/weather-cancellations', params as Record<string, unknown>)

  const downloadRevenueByOutletCsv = (params: ReportRangeParams) =>
    downloadCsv(
      'reports/revenue/by-outlet.csv',
      params,
      `revenue-by-outlet_${params.from_date}_${params.to_date}.csv`,
    )

  const downloadRevenueByServiceCsv = (params: ReportRangeParams) =>
    downloadCsv(
      'reports/revenue/by-service.csv',
      params,
      `revenue-by-service_${params.from_date}_${params.to_date}.csv`,
    )

  const downloadRevenueByPaymentMethodCsv = (params: ReportRangeParams) =>
    downloadCsv(
      'reports/revenue/by-payment-method.csv',
      params,
      `revenue-by-payment-method_${params.from_date}_${params.to_date}.csv`,
    )

  const downloadWeatherCancellationsCsv = (params: ReportRangeParams) =>
    downloadCsv(
      'reports/weather-cancellations.csv',
      params,
      `weather-cancellations_${params.from_date}_${params.to_date}.csv`,
    )

  return {
    today,
    revenueByOutlet,
    revenueByService,
    revenueByPaymentMethod,
    weatherCancellations,
    downloadRevenueByOutletCsv,
    downloadRevenueByServiceCsv,
    downloadRevenueByPaymentMethodCsv,
    downloadWeatherCancellationsCsv,
  }
}
