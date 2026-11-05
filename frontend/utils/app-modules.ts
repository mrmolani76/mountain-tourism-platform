import type { NavLink, VerticalNavItems } from '@layouts/types'

export interface AppModuleInfo {
  key: string
  title: string
  icon: string
  description: string
  endpoint: string
  features: Array<string>
}

/**
 * Mountain tourism modules that have live backend APIs.
 * Only implemented modules are listed, so the menu never links to empty screens.
 */
export const appModules: Array<AppModuleInfo> = [
  {
    key: 'folio',
    title: 'Folio & Billing',
    icon: 'tabler-receipt',
    description: 'Guest folios, charges and payments. Money is append-only.',
    endpoint: '/api/folios/{folio}',
    features: ['Open and close folios', 'Post charges and record payments', 'Void with reason, never edit'],
  },
]

export function getAppModule(key: string): AppModuleInfo | undefined {
  return appModules.find(module => module.key === key)
}

/** Menu links use route objects, not route names. */
export const appNavigation: VerticalNavItems = [
  {
    title: 'Dashboard',
    to: { path: '/' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    heading: 'Operations',
  },
  ...appModules.map(module => ({
    title: module.title,
    to: { path: `/modules/${module.key}` },
    icon: { icon: module.icon },
  })),
  {
    heading: 'Account',
  },
  {
    title: 'Profile',
    to: { path: '/user-profile' },
    icon: { icon: 'tabler-user-circle' },
  },
]
