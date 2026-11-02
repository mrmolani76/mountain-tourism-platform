import type { NavLink, NavLinkProps } from '@layouts/types'

export function resolveRoutes(menu: NavLink[]) {
  const router = useRouter()

  if (!menu?.length)
    return

  menu.forEach(x => {
    x.to = { name: router.resolve(x.to).name }
    if (x.children?.length)
      resolveRoutes(x.children)
  })

  return menu
}

export const getCustomComputedNavLinkToProp = computed(() => (link: NavLink) => {
  const props: NavLinkProps = {
    target: link.target,
    rel: link.rel,
  }

  // If route is string => it assumes string is route name => Create route object from route name
  // If route is not string => It assumes it's route object => returns passed route object
  if (link.to)
    props.to = link.to
  else props.href = link.href

  return props
})
