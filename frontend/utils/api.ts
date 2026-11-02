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
 * Central API client for the Django tenant API.
 *
 * - Base URL is `${apiBaseUrl}/api` (empty apiBaseUrl means same-origin `/api`).
 * - Session auth uses the `sessionid` cookie.
 * - Unsafe methods send `X-CSRFToken` from the Django `csrftoken` cookie.
 * - Errors use the backend envelope `{ success: false, error: { message } }`.
 */
export const $api = $fetch.create({
  onRequest({ options }) {
    const base = String(useRuntimeConfig().public.apiBaseUrl || '').replace(/\/$/, '')

    options.baseURL = `${base}/api`
    options.credentials = 'include'

    const method = (options.method || 'GET').toString().toUpperCase()

    if (UNSAFE_METHODS.has(method)) {
      const token = readCookie('csrftoken')

      if (token) {
        options.headers = new Headers(options.headers || {})
        options.headers.set('X-CSRFToken', token)
      }
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
    const isSessionProbe = url.includes('/auth/me/')
    const isLogout = url.includes('/auth/logout/')

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
