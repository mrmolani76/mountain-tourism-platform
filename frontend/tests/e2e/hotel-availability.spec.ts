import { test, expect } from '@playwright/test'

const EMAIL = process.env.E2E_STAFF_EMAIL || 'staff@example.com'
const PASSWORD = process.env.E2E_STAFF_PASSWORD || 'password'

async function login(page: any) {
  await page.goto('/login')
  await page.getByLabel(/email/i).fill(EMAIL)
  await page.getByLabel(/password/i).fill(PASSWORD)
  await page.getByRole('button', { name: /login/i }).click()
  await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible()
}

test.describe('Hotel — availability', () => {
  test.beforeEach(async ({ page }) => {
    await login(page)
    await page.goto('/hotel/availability')
    await expect(page.getByRole('heading', { name: /availability/i })).toBeVisible()
  })

  test('page loads date pickers, building select, and check availability button', async ({
    page,
  }) => {
    await expect(page.getByLabel(/check in/i)).toBeVisible()
    await expect(page.getByLabel(/check out/i)).toBeVisible()
    await expect(page.getByLabel(/building/i)).toBeVisible()
    await expect(page.getByRole('button', { name: /check availability/i })).toBeVisible()
  })

  test('check out before check in shows warning', async ({ page }) => {
    const checkIn = page.getByLabel(/check in/i)
    const checkOut = page.getByLabel(/check out/i)

    await checkIn.fill('2026-12-10')
    await checkOut.fill('2026-12-01')

    await expect(page.getByText(/check out must be after check in/i)).toBeVisible()
  })

  test('check availability loads room + bed tables', async ({ page }) => {
    await page.getByRole('button', { name: /check availability/i }).click()

    await expect(page.getByText(/rooms and chalets/i)).toBeVisible()
    await expect(page.getByText(/hut beds/i)).toBeVisible()
    await expect(page.getByText(/free/i)).toBeVisible()
  })
})
