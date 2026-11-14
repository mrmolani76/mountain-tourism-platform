import { test, expect } from '@playwright/test'

const EMAIL = process.env.E2E_STAFF_EMAIL || 'staff@example.com'
const PASSWORD = process.env.E2E_STAFF_PASSWORD || 'password'

test.describe('Auth — staff login / logout', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login')
  })

  test('login page renders with email + password fields and submit button', async ({
    page,
  }) => {
    await expect(page.getByLabel(/email/i)).toBeVisible()
    await expect(page.getByLabel(/password/i)).toBeVisible()
    await expect(page.getByRole('button', { name: /login/i })).toBeVisible()
  })

  test('successful login redirects to dashboard', async ({ page }) => {
    await page.getByLabel(/email/i).fill(EMAIL)
    await page.getByLabel(/password/i).fill(PASSWORD)
    await page.getByRole('button', { name: /login/i }).click()

    // Dashboard shows user + tenant + backend health cards
    await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible()
    await expect(page.getByText(/signed in as/i)).toBeVisible()
    await expect(page.getByText(/tenant/i)).toBeVisible()
  })

  test('invalid credentials show an error and stay on /login', async ({ page }) => {
    await page.getByLabel(/email/i).fill('nope@example.com')
    await page.getByLabel(/password/i).fill('wrong-password')
    await page.getByRole('button', { name: /login/i }).click()

    await expect(page).toHaveURL(/\/login/)
    await expect(
      page.getByText(/invalid|incorrect|credentials/i),
    ).toBeVisible()
  })

  test('empty submit shows validation errors', async ({ page }) => {
    await page.getByRole('button', { name: /login/i }).click()

    await expect(page.getByText(/required/i)).toBeVisible()
  })

  test('logout returns to /login', async ({ page }) => {
    // log in first
    await page.getByLabel(/email/i).fill(EMAIL)
    await page.getByLabel(/password/i).fill(PASSWORD)
    await page.getByRole('button', { name: /login/i }).click()
    await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible()

    // open user menu and logout (selectors match Vuetify toolbar pattern)
    await page.getByRole('button', { name: /user profile/i }).click()
    await page.getByRole('menuitem', { name: /logout/i }).click()

    await expect(page).toHaveURL(/\/login/)
  })
})
