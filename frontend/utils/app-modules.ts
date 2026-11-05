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
    key: 'hotel',
    title: 'Hotel',
    icon: 'tabler-bed',
    description: 'Rooms, board plans and guest stays across the complex.',
    endpoint: '/api/hotel/',
    features: ['Board plans and room types', 'Rooms per building', 'Check-in and check-out'],
  },
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

/** Hotel screens, grouped under the Hotel menu. */
export const hotelNavigation: Array<NavLink> = [
  {
    title: 'Overview',
    to: { path: '/modules/hotel' },
    icon: { icon: 'tabler-layout-grid' },
  },
  {
    title: 'Room types',
    to: { path: '/modules/hotel/room-types' },
  },
  {
    title: 'Rooms and chalets',
    to: { path: '/modules/hotel/rooms' },
  },
  {
    title: 'Hut dormitories',
    to: { path: '/modules/hotel/hut-dormitories' },
  },
  {
    title: 'Hut beds',
    to: { path: '/modules/hotel/hut-beds' },
  },
  {
    title: 'Season prices',
    to: { path: '/modules/hotel/season-prices' },
  },
  {
    title: 'Board plans',
    to: { path: '/modules/hotel/board-plans' },
  },
  {
    title: 'Reservations',
    to: { path: '/modules/hotel/reservations' },
  },
  {
    title: 'Stays',
    to: { path: '/modules/hotel/stays' },
  },
  {
    title: 'Availability',
    to: { path: '/modules/hotel/availability' },
  },
]

/**
 * Menu links use route objects, not route names.
 * `@layouts/utils` treats a string `to` value as a route name and would fail.
 */
export const appNavigation: VerticalNavItems = [
  {
    title: 'Dashboard',
    to: { path: '/' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    heading: 'Operations',
  },
  ...appModules.map(module => module.key === 'hotel'
    ? {
      title: module.title,
      icon: { icon: module.icon },
      children: hotelNavigation,
    }
    : {
      title: module.title,
      to: { path: `/modules/${module.key}` },
      icon: { icon: module.icon },
    }),
  {
    heading: 'Account',
  },
  {
    title: 'Profile',
    to: { path: '/user-profile' },
    icon: { icon: 'tabler-user-circle' },
  },
]
