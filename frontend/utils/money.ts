/**
 * Money helpers. The API stores integer minor units (cents), the UI shows major units.
 */

const formatter = new Intl.NumberFormat('en-IE', {
  style: 'currency',
  currency: 'EUR',
  minimumFractionDigits: 2,
})

export function formatCents(cents: number | null | undefined): string {
  if (cents === null || cents === undefined || Number.isNaN(Number(cents)))
    return formatter.format(0)

  return formatter.format(Number(cents) / 100)
}

export function centsToMajor(cents: number | null | undefined): number {
  return Number(((cents ?? 0) / 100).toFixed(2))
}

export function majorToCents(amount: number | string | null | undefined): number {
  const value = Number(amount ?? 0)

  if (Number.isNaN(value))
    return 0

  return Math.round(value * 100)
}
