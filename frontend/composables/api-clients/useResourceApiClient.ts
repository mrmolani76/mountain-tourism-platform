/**
 * Generic list/show/create/update/delete client for Laravel envelope APIs
 * (`{ success, data, meta }` lists or bare resource objects).
 * Used by Core CRUD pages and Staff list.
 */

interface Envelope<T> {
  success?: boolean
  data?: T
  error?: unknown
  meta?: Record<string, unknown>
}

export interface ResourceListMeta {
  page: number
  page_size: number
  total_pages: number
  total_count: number
  has_next: boolean
  has_previous: boolean
}

const emptyMeta = (): ResourceListMeta => ({
  page: 1,
  page_size: 20,
  total_pages: 1,
  total_count: 0,
  has_next: false,
  has_previous: false,
})

const cleanParams = (params: Record<string, unknown>) => {
  const query: Record<string, string> = {}

  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null || value === '')
      return

    query[key] = String(value)
  })

  return query
}

export const useResourceApiClient = () => {
  const { get, post, patch, del } = useApiClient()
  const loading = ref(false)

  const list = async <T,>(endpoint: string, params: Record<string, unknown> = {}) => {
    loading.value = true

    const response = await get<Envelope<Array<T>>>(endpoint, cleanParams(params))

    loading.value = false

    if (!response?.success) {
      return { data: [] as Array<T>, meta: emptyMeta() }
    }

    return {
      data: Array.isArray(response.data) ? response.data : [],
      meta: { ...emptyMeta(), ...(response.meta as Partial<ResourceListMeta> ?? {}) },
    }
  }

  const show = async <T,>(endpoint: string, id: number): Promise<T | null> => {
    loading.value = true
    const response = await get<T>(`${endpoint}/${id}`)

    loading.value = false

    return response ?? null
  }

  const create = async <T,>(endpoint: string, body: Record<string, unknown>): Promise<T | null> => {
    loading.value = true
    const response = await post<T>(endpoint, body)

    loading.value = false

    return response ?? null
  }

  const update = async <T,>(endpoint: string, id: number, body: Record<string, unknown>): Promise<T | null> => {
    loading.value = true
    const response = await patch<T>(`${endpoint}/${id}`, body)

    loading.value = false

    return response ?? null
  }

  const remove = async (endpoint: string, id: number): Promise<boolean> => {
    loading.value = true
    const response = await del(`${endpoint}/${id}`)

    loading.value = false

    // 204 → null body still counts as success (API returns null on catch only for errors)
    return response !== undefined
  }

  return { loading, list, show, create, update, remove }
}
