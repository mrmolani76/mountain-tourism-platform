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
 * Live modules for dashboard cards + fallback nav.
 * MVP focus: Core + Hotel only (EQMS-style page paths, no /modules prefix).
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
]

export function getAppModule(key: string): AppModuleInfo | undefined {
  return appModules.find(module => module.key === key)
}

/** Hotel screens (static fallback). Paths match eQMS: /hotel/... */
export const hotelNavigation: Array<NavLink> = [
  {
    title: 'Overview',
    to: { path: '/hotel' },
    icon: { icon: 'tabler-layout-grid' },
  },
  {
    title: 'Room types',
    to: { path: '/hotel/room-types' },
  },
  {
    title: 'Rooms and chalets',
    to: { path: '/hotel/rooms' },
  },
  {
    title: 'Hut dormitories',
    to: { path: '/hotel/hut-dormitories' },
  },
  {
    title: 'Hut beds',
    to: { path: '/hotel/hut-beds' },
  },
  {
    title: 'Season prices',
    to: { path: '/hotel/season-prices' },
  },
  {
    title: 'Board plans',
    to: { path: '/hotel/board-plans' },
  },
  {
    title: 'Reservations',
    to: { path: '/hotel/reservations' },
  },
  {
    title: 'Stays',
    to: { path: '/hotel/stays' },
  },
  {
    title: 'Availability',
    to: { path: '/hotel/availability' },
  },
]

const coreNavigation: Array<NavLink> = [
  {
    title: 'Staff',
    to: { path: '/users' },
    icon: { icon: 'tabler-users' },
  },
  {
    title: 'Buildings',
    to: { path: '/buildings' },
    icon: { icon: 'tabler-building' },
  },
  {
    title: 'Seasons',
    to: { path: '/seasons' },
    icon: { icon: 'tabler-calendar' },
  },
  {
    title: 'Settings',
    to: { path: '/general-settings' },
    icon: { icon: 'tabler-adjustments' },
  },
]

/**
 * Static fallback navigation (Core + Hotel only).
 * Prefer server menu from `GET /api/core/menus`.
 */
export const appNavigation: VerticalNavItems = [
  {
    title: 'Dashboard',
    to: { path: '/' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    title: 'Core',
    icon: { icon: 'tabler-settings' },
    children: coreNavigation,
  },
  {
    title: 'Hotel',
    icon: { icon: 'tabler-bed' },
    children: hotelNavigation,
  },
  {
    title: 'Profile',
    to: { path: '/user-profile' },
    icon: { icon: 'tabler-user-circle' },
  },
]
