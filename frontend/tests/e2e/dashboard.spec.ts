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

test.describe('Dashboard — health + user + tenant cards', () => {
  test('loads dashboard and shows staff info', async ({ page }) => {
    await login(page)

    await expect(page.getByText(/mountain complex operations at a glance/i)).toBeVisible()
    await expect(page.getByText(/signed in as/i)).toBeVisible()
    await expect(page.getByText(/tenant/i)).toBeVisible()
    await expect(page.getByText(/backend/i)).toBeVisible()
    await expect(page.getByText(/modules/i)).toBeVisible()
  })

  test('check API button flips health card between checking/online/offline', async ({
    page,
  }) => {
    await login(page)

    const healthBtn = page.getByRole('button', { name: /check api/i })
    await healthBtn.click()

    // Health chip should not stay 'checking' forever; assert it settles.
    await expect(page.getByText(/api online|api offline/)).toBeVisible({ timeout: 15000 })
  })

  test('module cards link to their route', async ({ page }) => {
    await login(page)

    const hotelCard = page.getByRole('link', { name: /hotel/i }).first()
    await expect(hotelCard).toBeVisible()
    await hotelCard.click()
    await expect(page).toHaveURL(/\/hotel\/?$/)
  })
})
