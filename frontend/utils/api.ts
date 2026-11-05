/**
 * Legacy `$api` helper.
 *
 * Prefer `API` from `@/composables/core/useApi` (eQMS qualitycenter-spa pattern)
 * or `useApiClient` / domain services. Kept so older call sites compile.
 *
 * New code: `import { API } from '@/composables/core/useApi'`.
 */

import { API } from '@/composables/core/useApi'
import { resolveApiOrigin } from '@/utils/tenantHost'

const UNSAFE_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE'])

function readCookie(name: string): string | null {
  if (typeof document === 'undefined')
    return null

  const prefix = `${name}=`
  const match = document.cookie.split('; ').find(row => row.startsWith(prefix))

  return match ? decodeURIComponent(match.slice(prefix.length)) : null
}

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

export const $api = $fetch.create({
  timeout: 8_000,
  async onRequest({ options }) {
    const method = (options.method || 'GET').toString().toUpperCase()

    // Mutating calls only — matches MountainApi (avoid CSRF hang before every GET).
    if (UNSAFE_METHODS.has(method))
      await ensureLaravelCsrfCookie()

    const origin = resolveApiOrigin()

    options.baseURL = `${origin}/api`
    options.credentials = 'include'

    if (UNSAFE_METHODS.has(method)) {
      options.headers = new Headers(options.headers || {})
      csrfHeaders(options.headers)
    }
  },

  onResponseError({ response }) {
    const router = useRouter()
    const { unauthenticate, openLoginDialog } = authStore()
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

      if (!isSessionProbe && !isLogout) {
        $toast.error(message)
        openLoginDialog()
      }

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
 * Laravel Sanctum CSRF cookie (EQMS: `axios.get(.../sanctum/csrf-cookie)`).
 * No-op when XSRF-TOKEN already present. Always uses current host origin.
 */
export async function ensureLaravelCsrfCookie(): Promise<void> {
  // Shared cooldown + timeout live on the MountainApi singleton.
  await API.ensureCsrfCookie()
}

/** Re-export for callers that want the EQMS singleton by name. */
export { API }
