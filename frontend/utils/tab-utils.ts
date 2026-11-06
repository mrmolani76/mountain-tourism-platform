/**
 * Keep the active AppTabs key in the URL hash so refresh / share / back
 * restore the same tab (EQMS pattern: `#userInfo`).
 */
export const pushHashIntoRoute = (hash: string | null | undefined) => {
  if (!hash)
    return

  const router = useRouter()
  const next = `#${String(hash).replace(/^#/, '')}`

  if (router.currentRoute.value.hash === next)
    return

  router.push({ hash: next })
}
