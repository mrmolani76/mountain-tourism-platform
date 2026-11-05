import { IS_SAAS_ADMIN_HOST, resolveApiOrigin } from '@/utils/tenantHost'

/**
 * Shared HTTP client — eQMS qualitycenter-spa `composables/core/useApi.ts` pattern.
 *
 * - Sanctum CSRF: only before mutating methods when XSRF-TOKEN missing
 * - Short timeout so a dead Vite→Laravel proxy fails fast (not 30–120s hang)
 * - CSRF failure cooldown avoids stacking timeouts on every parallel call
 * - baseURL: `{origin}/api` (no trailing slash on paths; Laravel Core routes)
 */

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

/** Fail fast when Laravel is down / proxy cannot connect. */
const REQUEST_TIMEOUT_MS = 8_000

/** After CSRF fails, skip re-trying for this window (ms). */
const CSRF_FAIL_COOLDOWN_MS = 5_000

const UNSAFE_METHODS = new Set<HttpMethod>(['POST', 'PUT', 'PATCH', 'DELETE'])

function readCookie(name: string): string | null {
  if (typeof document === 'undefined')
    return null

  const prefix = `${name}=`
  const match = document.cookie.split('; ').find(row => row.startsWith(prefix))

  return match ? decodeURIComponent(match.slice(prefix.length)) : null
}

function stripTrailingSlash(url: string): string {
  if (!url || url === '/')
    return url

  return url.replace(/\/+$/, '')
}

function timeoutSignal(ms: number): AbortSignal | undefined {
  if (typeof AbortSignal !== 'undefined' && typeof AbortSignal.timeout === 'function')
    return AbortSignal.timeout(ms)

  return undefined
}

class MountainApi {
  private csrfTokenPromise: Promise<void> | null = null
  private csrfCooldownUntil = 0

  /**
   * Seed XSRF cookie. Safe GETs skip this (session cookie is enough to probe `/auth/me`).
   * Mutating calls still require it for Sanctum SPA.
   */
  async ensureCsrfCookie(): Promise<void> {
    if (readCookie('XSRF-TOKEN'))
      return

    if (Date.now() < this.csrfCooldownUntil)
      return

    if (!this.csrfTokenPromise) {
      const origin = resolveApiOrigin()

      this.csrfTokenPromise = $fetch(`${origin}/sanctum/csrf-cookie`, {
        method: 'GET',
        credentials: 'include',
        signal: timeoutSignal(REQUEST_TIMEOUT_MS),
        timeout: REQUEST_TIMEOUT_MS,
      })
        .then(() => {
          this.csrfTokenPromise = null
          this.csrfCooldownUntil = 0
        })
        .catch(() => {
          this.csrfTokenPromise = null
          this.csrfCooldownUntil = Date.now() + CSRF_FAIL_COOLDOWN_MS
        })
    }

    await this.csrfTokenPromise
  }

  private async request<T>(method: HttpMethod, url: string, body?: unknown): Promise<T | null> {
    if (UNSAFE_METHODS.has(method))
      await this.ensureCsrfCookie()

    const origin = resolveApiOrigin()
    const path = stripTrailingSlash(url.replace(/^\//, ''))
    const headers: Record<string, string> = {}

    const xsrf = readCookie('XSRF-TOKEN')
    if (xsrf)
      headers['X-XSRF-TOKEN'] = xsrf

    const isFormData = typeof FormData !== 'undefined' && body instanceof FormData

    if (method !== 'GET' && !isFormData)
      headers['Content-Type'] = 'application/json'

    try {
      return await $fetch<T>(`${origin}/api/${path}`, {
        method,
        body: method === 'GET' ? undefined : body,
        credentials: 'include',
        headers,
        signal: timeoutSignal(REQUEST_TIMEOUT_MS),
        timeout: REQUEST_TIMEOUT_MS,
      })
    }
    catch (error: any) {
      await this.errorHandler(error)

      return null
    }
  }

  private async errorHandler(error: any): Promise<void> {
    const router = useRouter()
    const { $toast } = useNuxtApp()
    const status = error?.response?.status ?? error?.statusCode ?? error?.status
    const data = error?.response?._data ?? error?.data ?? error
    const message = data?.error?.message || data?.detail || data?.message || 'Request failed.'
    const url = String(error?.request || error?.response?.url || '')

    const isAuthProbe = url.includes('/auth/me') || url.includes('/saas/auth/')
    const isLogout = url.includes('/auth/logout')
    const isTimeout = error?.name === 'TimeoutError'
      || error?.code === 'TIMEOUT'
      || error?.cause?.name === 'TimeoutError'
      || String(error?.message || '').toLowerCase().includes('timeout')

    if (isTimeout && !isAuthProbe) {
      $toast.error('API timed out. Is the Laravel backend running?')

      return
    }

    if (status === 401) {
      if (IS_SAAS_ADMIN_HOST) {
        if (!url.includes('saas/auth/'))
          await navigateTo('/saas/login')
      }
      else {
        const { unauthenticate, openLoginDialog } = authStore()

        unauthenticate()
        if (!isAuthProbe && !isLogout) {
          $toast.error(message)
          openLoginDialog()
        }
        if (!isAuthProbe && router.currentRoute.value.path !== '/login')
          await navigateTo('/login')
      }

      return
    }

    if (status === 403)
      $toast.error(message)
    else if (status && status >= 400 && !isAuthProbe)
      $toast.error(message)
  }

  get = async <T>(url: string) => this.request<T>('GET', url)
  post = async <T>(url: string, data: object = {}) => this.request<T>('POST', url, data)
  put = async <T>(url: string, data: object = {}) => this.request<T>('PUT', url, data)
  patch = async <T>(url: string, data: object = {}) => this.request<T>('PATCH', url, data)
  delete = async <T>(url: string, data: object = {}) => this.request<T>('DELETE', url, data)
}

/** Singleton — eQMS exports `API = new useApi()`. */
export const API = new MountainApi()

/** Factory alias matching eQMS `useApi` name for discoverability. */
export const useApi = () => API
