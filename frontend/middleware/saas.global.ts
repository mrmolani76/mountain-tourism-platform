import { IS_SAAS_ADMIN_HOST } from '@/utils/tenantHost'

export default defineNuxtRouteMiddleware(async to => {
  const isSaasRoute = to.path === '/saas' || to.path.startsWith('/saas/')

  if (!IS_SAAS_ADMIN_HOST) {
    if (isSaasRoute)
      return navigateTo('/login', { replace: true })

    return
  }

  if (!isSaasRoute)
    return navigateTo('/saas', { replace: true })

  const auth = useSaasAuth()

  if (to.path === '/saas/login') {
    if (auth.isAuthenticated.value) {
      const target = typeof to.query.to === 'string' ? to.query.to : '/saas'

      return navigateTo(target.startsWith('/saas') ? target : '/saas', { replace: true })
    }

    return
  }

  if (auth.authenticated.value === undefined)
    await auth.fetchUser()

  if (!auth.isAuthenticated.value) {
    return navigateTo({
      path: '/saas/login',
      query: { to: to.fullPath },
    }, { replace: true })
  }
})
