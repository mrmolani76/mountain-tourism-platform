export default defineNuxtRouteMiddleware(async to => {
  const store = authStore()

  // Pure public pages can render without probing the session. Guest-only pages
  // (login) still probe, so a live session bounces the user back to the app.
  if (to.meta.public && !to.meta.unauthenticatedOnly)
    return

  if (store.authenticated === undefined) {
    try {
      await store.saveUser()
    }
    catch {
      // A failed bootstrap probe must not crash Nuxt's error screen.
      store.unauthenticate()
    }
  }

  const isLoggedIn = store.isAuthenticated === true

  if (isLoggedIn) {
    if (to.meta.unauthenticatedOnly)
      return navigateTo('/', { replace: true })
  }
  else if (!to.meta.public) {
    return navigateTo({
      path: '/login',
      query: { to: to.path },
    })
  }

  if (isLoggedIn && to.meta?.checkPermission) {
    const userPermissions = store.getUserPermissions

    const checkPermission = (to.meta.form && to.params.id
      ? to.meta.checkPermissionEdit
      : to.meta.checkPermission) as string

    if (userPermissions && userPermissions.includes(checkPermission))
      return

    return navigateTo('/unauthorized')
  }
})
