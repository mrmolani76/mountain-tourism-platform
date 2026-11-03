function stripTrailingSlash(url: string): string {
  if (!url || url === '/')
    return url

  return url.replace(/\/+$/, '')
}

export const useApiClient = () => {
  const get = async <T>(url: string, query?: Record<string, unknown>) => {
    try {
      const queryString = query
        ? `?${new URLSearchParams(query as Record<string, string>).toString()}`
        : ''

      return await $api<T>(`${stripTrailingSlash(url)}${queryString}`)
    }
    catch {
      return null
    }
  }

  const post = async <T>(url: string, body: any) => {
    try {
      const isFormData = typeof FormData !== 'undefined' && body instanceof FormData

      return await $api<T>(stripTrailingSlash(url), {
        method: 'POST',
        body,
        headers: isFormData ? {} : { 'Content-Type': 'application/json' },
      })
    }
    catch {
      return null
    }
  }

  const put = async <T>(url: string, body: any) => {
    try {
      const isFormData = typeof FormData !== 'undefined' && body instanceof FormData

      return await $api<T>(stripTrailingSlash(url), {
        method: 'PUT',
        body,
        headers: isFormData ? {} : { 'Content-Type': 'application/json' },
      })
    }
    catch {
      return null
    }
  }

  const del = async <T>(url: string, body: any) => {
    try {
      return await $api<T>(stripTrailingSlash(url), {
        method: 'DELETE',
        body,
        headers: { 'Content-Type': 'application/json' },
      })
    }
    catch {
      return null
    }
  }

  return { get, post, put, del }
}
