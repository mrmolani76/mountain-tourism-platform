/**
 * Host → tenant / SaaS detection (eQMS qualitycenter-spa `utils/common.ts` pattern).
 *
 * Local scheme (Module 1):
 *   lab.localhost     → tenant `lab`
 *   saas.localhost    → central SaaS admin
 *   *.localhost       → tenant slug = first label
 *
 * Optional later: `{slug}.local.mountain.local` (Complere-style dots).
 */

export function getEnvAndTenantFromHost(hostname?: string): {
  TENANT_SLUG: string | null
  ENVIRONMENT: string | null
  IS_SAAS_ADMIN_HOST: boolean
  API_ORIGIN: string
} {
  if (typeof window === 'undefined' && !hostname) {
    return {
      TENANT_SLUG: null,
      ENVIRONMENT: null,
      IS_SAAS_ADMIN_HOST: false,
      API_ORIGIN: '',
    }
  }

  const host = (hostname || window.location.hostname).toLowerCase()
  const origin = typeof window !== 'undefined'
    ? window.location.origin
    : ''

  // Complere-like: lab.local.mountain.local / saas.local.mountain.local
  if (host.endsWith('.local.mountain.local') || host.endsWith('.mountain.local')) {
    const prefix = host
      .replace(/\.local\.mountain\.local$/, '')
      .replace(/\.mountain\.local$/, '')
    const slug = prefix.split('.')[0] || null

    return {
      TENANT_SLUG: slug === 'saas' ? null : slug,
      ENVIRONMENT: 'local',
      IS_SAAS_ADMIN_HOST: slug === 'saas',
      API_ORIGIN: origin,
    }
  }

  // Dev shorthand: lab.localhost / saas.localhost
  if (host.endsWith('.localhost') || host === 'localhost' || host === '127.0.0.1') {
    const slug = host.includes('.') ? host.split('.')[0] : 'lab'

    return {
      TENANT_SLUG: slug === 'saas' ? null : (host === 'localhost' || host === '127.0.0.1' ? 'lab' : slug),
      ENVIRONMENT: 'local',
      IS_SAAS_ADMIN_HOST: slug === 'saas',
      API_ORIGIN: origin,
    }
  }

  return {
    TENANT_SLUG: null,
    ENVIRONMENT: null,
    IS_SAAS_ADMIN_HOST: false,
    API_ORIGIN: origin,
  }
}

const clientInfo = getEnvAndTenantFromHost()

export const TENANT_SLUG = clientInfo.TENANT_SLUG
export const SPA_ENVIRONMENT = clientInfo.ENVIRONMENT
export const IS_SAAS_ADMIN_HOST = clientInfo.IS_SAAS_ADMIN_HOST

/**
 * Same-origin API root for the current host (EQMS: SAAS_CUSTOMER_URL).
 * Empty NUXT_PUBLIC_API_BASE_URL → browser origin (nginx proxies /api).
 */
export function resolveApiOrigin(): string {
  try {
    const configured = String(useRuntimeConfig().public.apiBaseUrl || '').replace(/\/$/, '')
    if (configured)
      return configured
  }
  catch {
    // Outside Nuxt setup (rare); fall through to window origin.
  }

  if (typeof window !== 'undefined')
    return window.location.origin

  return ''
}
