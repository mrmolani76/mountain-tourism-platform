import { IS_SAAS_ADMIN_HOST, resolveApiOrigin } from '@/utils/tenantHost'

/**
 * Shared HTTP client for the Mountain Nuxt SPA.
 *
 * - Sanctum CSRF: bootstrap before mutations and recover once from HTTP 419
 * - Bounded timeout that allows slow local Laravel startup and login requests
 * - CSRF failure cooldown avoids stacking timeouts on every parallel call
 * - baseURL: `{origin}/api` (no trailing slash on paths; Laravel Core routes)
 */

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

/**
 * Local Laravel requests can take over 30 seconds with the Windows bind mount.
 * Keep the timeout bounded while allowing CSRF bootstrap and login to finish.
 */
const REQUEST_TIMEOUT_MS = 60_000

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

function clearXsrfCookie(): void {
  if (typeof document === 'undefined')
    return

  document.cookie = 'XSRF-TOKEN=; Path=/; Max-Age=0; SameSite=Lax'
  document.cookie = 'XSRF-TOKEN=; Path=/; Max-Age=0; SameSite=Lax; Secure'
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
  private csrfRecoveryPromise: Promise<boolean> | null = null
  private csrfCooldownUntil = 0

  /**
   * Seed XSRF cookie. Safe GETs skip this (session cookie is enough to probe `/auth/me`).
   * Mutating calls still require it for Sanctum SPA.
   */
  async ensureCsrfCookie(): Promise<boolean> {
    if (readCookie('XSRF-TOKEN'))
      return true

    if (Date.now() < this.csrfCooldownUntil)
      return Boolean(readCookie('XSRF-TOKEN'))

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
          if (!readCookie('XSRF-TOKEN'))
            throw new Error('Laravel did not set the CSRF cookie.')
        })
        .catch((err: unknown) => {
          this.csrfTokenPromise = null
          this.csrfCooldownUntil = Date.now() + CSRF_FAIL_COOLDOWN_MS
          // Surface once per cooldown so a dead proxy is visible (not silent hang).
          try {
            const { $toast } = useNuxtApp()
            const msg = err && typeof err === 'object' && 'message' in err
              ? String((err as { message?: unknown }).message || '')
              : ''
            const timedOut = msg.toLowerCase().includes('timeout')
              || (err && typeof err === 'object' && 'name' in err && (err as { name?: string }).name === 'TimeoutError')
            $toast.error(timedOut
              ? 'API timed out reaching Laravel (check BACKEND_URL=http://127.0.0.1:8000 and artisan serve).'
              : 'Could not reach Laravel CSRF (/sanctum). Is the API running on :8000?')
          }
          catch {
            // Outside Nuxt setup.
          }
        })
    }

    await this.csrfTokenPromise

    return Boolean(readCookie('XSRF-TOKEN'))
  }

  private async recoverCsrfToken(): Promise<boolean> {
    if (this.csrfRecoveryPromise)
      return this.csrfRecoveryPromise

    const recovery = this.refreshCsrfToken()
    this.csrfRecoveryPromise = recovery

    return recovery
  }

  private async refreshCsrfToken(): Promise<boolean> {
    try {
      clearXsrfCookie()
      this.csrfCooldownUntil = 0
      this.csrfTokenPromise = null
      return await this.ensureCsrfCookie()
    }
    finally {
      this.csrfRecoveryPromise = null
    }
  }

  private async request<T>(
    method: HttpMethod,
    url: string,
    body?: unknown,
    csrfRetried = false,
  ): Promise<T | null> {
    if (UNSAFE_METHODS.has(method) && !(await this.ensureCsrfCookie()))
      return null

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
      const result = await $fetch<T>(`${origin}/api/${path}`, {
        method,
        body: method === 'GET' ? undefined : body,
        credentials: 'include',
        headers,
        signal: timeoutSignal(REQUEST_TIMEOUT_MS),
        timeout: REQUEST_TIMEOUT_MS,
      })

      // 204 No Content → undefined; treat as success token for DELETE callers.
      return (result === undefined || result === null) && method === 'DELETE'
        ? ({ ok: true } as T)
        : result
    }
    catch (error: any) {
      const status = error?.response?.status ?? error?.statusCode ?? error?.status
      if (status === 419 && UNSAFE_METHODS.has(method) && !csrfRetried) {
        const tokenRefreshed = await this.recoverCsrfToken()
        if (tokenRefreshed)
          return this.request<T>(method, url, body, true)
      }

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

    const isAuthProbe = url.includes('/auth/me')
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

/** Shared singleton HTTP client. */
export const API = new MountainApi()

/** Factory alias that returns the shared `API` singleton. */
export const useApi = () => API
