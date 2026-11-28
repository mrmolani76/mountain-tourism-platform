export interface SaasTenant {
  uuid: string
  name: string
  slug: string
  db_name?: string
  is_active: boolean
  domains: string[]
  primary_domain?: string | null
  created_at?: string
  owner_created?: boolean
  tenant_database_ready?: boolean
  tenant_migrations_ready?: boolean
  owner_email_delivery_status?: 'not_requested' | 'not_sent' | 'not_configured' | 'sent' | 'failed'
}

export interface SaasTenantCreateResponse extends SaasTenant {
  owner_temporary_password: string | null
  tenant_database_ready: boolean
  tenant_migrations_ready: boolean
  owner_email_delivery_status: 'not_requested' | 'not_sent' | 'not_configured' | 'sent' | 'failed'
}

export interface SaasTenantPage {
  success: boolean
  data: SaasTenant[]
  meta: {
    page: number
    page_size: number
    total_count: number
    total_pages: number
  }
}

export interface SaasReadinessItem {
  ready: boolean
  state?: string
  next_step?: string | null
}

export interface SaasReadiness {
  registry: SaasReadinessItem
  primary_domain: SaasReadinessItem
  tenant_database: SaasReadinessItem
  tenant_migrations: SaasReadinessItem
  owner_account: SaasReadinessItem
  ready: boolean
}

export interface SaasActivity {
  id: number
  actor_email: string
  event_type: string
  metadata: Record<string, unknown> | null
  created_at: string
}

export interface SaasDashboardActivity {
  id: number
  tenant_uuid: string | null
  tenant_name: string | null
  actor_email: string
  event_type: string
  created_at: string
}

export const useSaasApiClient = () => {
  const { get, post, patch } = useApiClient()

  const getDashboard = () => get<{
    total: number
    active: number
    inactive: number
    recent_activity: SaasDashboardActivity[]
  }>('saas/dashboard')

  const getTenants = (query: Record<string, string>) =>
    get<SaasTenantPage>('saas/tenants', query)

  const getTenant = (uuid: string) => get<SaasTenant>(`saas/tenants/${encodeURIComponent(uuid)}`)

  const createTenant = (body: {
    name: string
    slug: string
    base_domain?: string
    owner_email?: string
  }) => post<SaasTenantCreateResponse>('saas/tenants', body)

  const createOwner = (uuid: string, body: { owner_email: string }) =>
    post<{
      owner_created: boolean
      owner_temporary_password: string
      owner_email_delivery_status: 'not_configured' | 'sent' | 'failed'
    }>(`saas/tenants/${encodeURIComponent(uuid)}/owner`, body)

  const updateTenant = (uuid: string, body: { name: string }) =>
    patch<SaasTenant>(`saas/tenants/${encodeURIComponent(uuid)}`, body)

  const setTenantActive = (uuid: string, active: boolean) =>
    post<SaasTenant>(`saas/tenants/${encodeURIComponent(uuid)}/${active ? 'activate' : 'deactivate'}`)

  const getReadiness = (uuid: string) => get<SaasReadiness>(`saas/tenants/${encodeURIComponent(uuid)}/readiness`)

  const getActivity = (uuid: string) => get<Array<SaasActivity>>(`saas/tenants/${encodeURIComponent(uuid)}/activity`)

  return {
    getDashboard,
    getTenants,
    getTenant,
    createTenant,
    createOwner,
    updateTenant,
    setTenantActive,
    getReadiness,
    getActivity,
  }
}
