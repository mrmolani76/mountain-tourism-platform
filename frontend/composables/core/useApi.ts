import { IS_SAAS_ADMIN_HOST, resolveApiOrigin } from '@/utils/tenantHost'

/**
 * Shared HTTP client — eQMS qualitycenter-spa `composables/core/useApi.ts` pattern.
 *
 * - Sanctum CSRF: GET `{origin}/sanctum/csrf-cookie` once if XSRF-TOKEN missing
 * - Every call: credentials include + X-XSRF-TOKEN
 * - baseURL: `{origin}/api` (no trailing slash on paths; Laravel Core routes)
 * - Pages/components must NOT call $fetch directly — go through domain services
 */

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

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

class MountainApi {
  private csrfTokenPromise: Promise<void> | null = null

  private async ensureCsrfCookie(): Promise<void> {
    if (readCookie('XSRF-TOKEN'))
      return

    if (!this.csrfTokenPromise) {
      const origin = resolveApiOrigin()

      this.csrfTokenPromise = $fetch(`${origin}/sanctum/csrf-cookie`, {
        method: 'GET',
        credentials: 'include',
      })
        .then(() => {
          this.csrfTokenPromise = null
        })
        .catch(() => {
          this.csrfTokenPromise = null
        })
    }

    await this.csrfTokenPromise
  }

  private async request<T>(method: HttpMethod, url: string, body?: unknown): Promise<T | null> {
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

    if (status === 401) {
      if (IS_SAAS_ADMIN_HOST) {
        // SaaS admin store wiring lands with Module 1 SaaS pages.
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
