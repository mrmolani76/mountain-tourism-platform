import { API } from '@/composables/core/useApi'

function stripTrailingSlash(url: string): string {
  if (!url || url === '/')
    return url

  return url.replace(/\/+$/, '')
}

function normalizePath(url: string): string {
  return stripTrailingSlash(url.replace(/^\//, ''))
}

/**
 * Thin domain-agnostic HTTP helpers over the shared `API` singleton
 * Pages call domain clients; clients call `API` from `composables/core/useApi`.
 */
export const useApiClient = () => {
  const get = async <T>(url: string, query?: Record<string, unknown>) => {
    const path = normalizePath(url)
    const withQuery = query
      ? `${path}?${new URLSearchParams(query as Record<string, string>).toString()}`
      : path

    return await API.get<T>(withQuery)
  }

  const post = async <T>(url: string, body: any = {}) => {
    return await API.post<T>(normalizePath(url), body ?? {})
  }

  const put = async <T>(url: string, body: any = {}) => {
    return await API.put<T>(normalizePath(url), body ?? {})
  }

  const patch = async <T>(url: string, body: any = {}) => {
    return await API.patch<T>(normalizePath(url), body ?? {})
  }

  const del = async <T>(url: string, body: any = {}) => {
    return await API.delete<T>(normalizePath(url), body ?? {})
  }

  return { get, post, put, patch, del }
}
