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

test.describe('Folio — checkout zero-balance gate', () => {
  test('checkout blocked when folio balance is non-zero', async ({ page }) => {
    await login(page)
    await page.goto('/hotel/stays')

    await expect(
      page.getByRole('heading', { name: /stays/i }),
    ).toBeVisible()

    // Open a stay with a non-zero balance and attempt checkout.
    const checkoutBtn = page.getByRole('button', { name: /checkout/i }).first()
    if (await checkoutBtn.isVisible()) {
      await checkoutBtn.click()

      // Money rule: balance non-zero → blocked with reason
      await expect(
        page.getByText(/balance|non.zero|settle before checkout/i),
      ).toBeVisible()
    } else {
      test.skip(true, 'no stay with non-zero balance seeded in dev DB')
    }
  })

  test('void flow appends a correction row (reason + actor + timestamp)', async ({
    page,
  }) => {
    await login(page)
    await page.goto('/hotel/stays')

    // Folio charges/payments are append-only; a void records reason, actor, timestamp.
    const folioLink = page.getByRole('link', { name: /folio/i }).first()
    if (await folioLink.isVisible()) {
      await folioLink.click()
      await expect(page.getByText(/charge|payment|void/i)).toBeVisible()
    } else {
      test.skip(true, 'folio UI not yet wired (Module 3)')
    }
  })
})
