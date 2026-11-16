/**
 * Tenant settings from Laravel Core `GET|PATCH /api/settings`.
 */
export interface GeneralSetting {
  id?: number
  currency: string
  timezone: string
  units: 'metric' | 'imperial'
  /** Percent values, e.g. [7.7, 2.5] */
  tax_rates: number[]
}
