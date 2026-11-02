import type { VerticalNavItems } from '@layouts/types'

export interface AppModuleInfo {
  key: string
  title: string
  icon: string
  description: string
  endpoint: string
  features: Array<string>
}

/**
 * Mountain tourism modules backed by the Django tenant API.
 * Screens are placeholders until each module UI is implemented.
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
    endpoint: '/api/folio/',
    features: ['Open and close folios', 'Post charges and record payments', 'Void with reason, never edit'],
  },
  {
    key: 'pos',
    title: 'POS',
    icon: 'tabler-shopping-cart',
    description: 'Restaurant, hut kitchen and shop orders.',
    endpoint: '/api/pos/',
    features: ['Product categories and products', 'Open orders and order lines', 'Charge an order to a folio'],
  },
  {
    key: 'rental',
    title: 'Rental',
    icon: 'tabler-bike',
    description: 'Ski and equipment rental with deposits and damage fees.',
    endpoint: '/api/rental/',
    features: ['Rent out and return items', 'Deposits and damage fees', 'Link rental charges to a folio'],
  },
  {
    key: 'services',
    title: 'Services',
    icon: 'tabler-spa',
    description: 'Ski school, guided hikes, spa and other bookable services.',
    endpoint: '/api/services/',
    features: ['Service catalogue and prices', 'Time slots with capacity', 'Staff assignment per slot'],
  },
  {
    key: 'transport',
    title: 'Transport',
    icon: 'tabler-bus',
    description: 'Shuttles, taxis and pickups between buildings.',
    endpoint: '/api/transport/',
    features: ['Vehicles and drivers', 'Scheduled trips', 'Passenger counts'],
  },
  {
    key: 'weather',
    title: 'Weather',
    icon: 'tabler-cloud-snow',
    description: 'Weather limits and warnings for lifts and outdoor areas.',
    endpoint: '/api/weather/',
    features: ['Limits per weather type', 'Active warnings', 'Resolve warnings when conditions clear'],
  },
  {
    key: 'reports',
    title: 'Reports',
    icon: 'tabler-chart-bar',
    description: 'Revenue, occupancy and operational reports.',
    endpoint: '/api/reports/',
    features: ['Folio and revenue summaries', 'Occupancy reports', 'Rental and transport reports'],
  },
]

export function getAppModule(key: string): AppModuleInfo | undefined {
  return appModules.find(module => module.key === key)
}

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
