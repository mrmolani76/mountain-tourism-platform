const UNSAFE_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE'])

/**
 * Read a cookie without Nuxt composables.
 * The app runs as an SPA, so `document` is always available at request time.
 */
function readCookie(name: string): string | null {
  if (typeof document === 'undefined')
    return null

  const prefix = `${name}=`
  const match = document.cookie.split('; ').find(row => row.startsWith(prefix))

  return match ? decodeURIComponent(match.slice(prefix.length)) : null
}

/**
 * Laravel sets encrypted `XSRF-TOKEN`; Django used `csrftoken`.
 * Prefer Laravel header when present.
 */
function csrfHeaders(headers: Headers): void {
  const xsrf = readCookie('XSRF-TOKEN')
  if (xsrf) {
    headers.set('X-XSRF-TOKEN', xsrf)

    return
  }

  const django = readCookie('csrftoken')
  if (django)
    headers.set('X-CSRFToken', django)
}

/**
 * Central API client for the Laravel tenant API (Module Core).
 *
 * - Base URL is `${apiBaseUrl}/api` (empty apiBaseUrl = same-origin `/api` via nginx).
 * - Session auth uses cookies (`credentials: include`).
 * - Unsafe methods send Laravel `X-XSRF-TOKEN` (or Django CSRF while DRF still live).
 * - Errors use envelope `{ success: false, error: { message } }` or plain `{ detail }`.
 */
export const $api = $fetch.create({
  onRequest({ options }) {
    const base = String(useRuntimeConfig().public.apiBaseUrl || '').replace(/\/$/, '')

    options.baseURL = `${base}/api`
    options.credentials = 'include'

    const method = (options.method || 'GET').toString().toUpperCase()

    if (UNSAFE_METHODS.has(method)) {
      options.headers = new Headers(options.headers || {})
      csrfHeaders(options.headers)
    }
  },

  onResponseError({ response }) {
    const router = useRouter()
    const { unauthenticate } = authStore()
    const { $toast } = useNuxtApp()

    const body = (response?._data || {}) as Record<string, any>
    const message = body?.error?.message
      || body?.detail
      || body?.message
      || 'Request failed.'

    const status = response?.status
    const url = response?.url || ''
    const isSessionProbe = url.includes('/auth/me')
    const isLogout = url.includes('/auth/logout')

    if (status === 401) {
      unauthenticate()

      if (!isSessionProbe && !isLogout)
        $toast.error(message)

      if (router.currentRoute.value.path !== '/login')
        router.push('/login')
    }
    else if (status === 403) {
      $toast.error(message)
    }
    else if (status && status >= 400 && !isSessionProbe) {
      $toast.error(message)
    }

    throw body?.error ? body.error : body
  },
})

/**
 * Hit Laravel Sanctum CSRF cookie endpoint before the first unsafe call
 * when the SPA talks cross-origin to the API (not needed for same-origin nginx proxy).
 */
export async function ensureLaravelCsrfCookie(): Promise<void> {
  const base = String(useRuntimeConfig().public.apiBaseUrl || '').replace(/\/$/, '')
  if (!base)
    return

  await $fetch(`${base}/sanctum/csrf-cookie`, {
    method: 'GET',
    credentials: 'include',
  })
}
