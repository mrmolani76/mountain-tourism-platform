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
 * MVP focus: Core + Hotel + Folio + POS + Rental (root page paths, no /modules prefix).
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
    title: 'Folio',
    icon: 'tabler-receipt',
    description: 'Guest bills, charge to room, payments and voids.',
    endpoint: '/api/folios/',
    features: ['Open folios', 'Charge to room', 'Payments and voids'],
  },
  {
    key: 'pos',
    title: 'POS',
    icon: 'tabler-tools-kitchen-2',
    description: 'Restaurant and shop orders, shifts and catalog.',
    endpoint: '/api/pos/',
    features: ['Cashier shifts', 'Orders and settle', 'Board-included meals'],
  },
  {
    key: 'rental',
    title: 'Rental',
    icon: 'tabler-skiing',
    description: 'Equipment rent out, returns, late and damage fees.',
    endpoint: '/api/rental/',
    features: ['Rent out desk', 'Currently out', 'Season day rates'],
  },
]

export function getAppModule(key: string): AppModuleInfo | undefined {
  return appModules.find(module => module.key === key)
}

/** Hotel screens (static fallback). Paths under /hotel/... */
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

/** Folio screens (static fallback). Paths under /folios/... */
export const folioNavigation: Array<NavLink> = [
  {
    title: 'All folios',
    to: { path: '/folios' },
    icon: { icon: 'tabler-list' },
  },
  {
    title: 'Charge to room',
    to: { path: '/folios/charge-to-room' },
    icon: { icon: 'tabler-door' },
  },
]

/** POS screens (static fallback). Paths under /pos/... */
export const posNavigation: Array<NavLink> = [
  {
    title: 'Overview',
    to: { path: '/pos' },
    icon: { icon: 'tabler-layout-grid' },
  },
  {
    title: 'Orders',
    to: { path: '/pos/orders' },
    icon: { icon: 'tabler-receipt-2' },
  },
  {
    title: 'Shifts',
    to: { path: '/pos/shifts' },
    icon: { icon: 'tabler-clock' },
  },
  {
    title: 'Products',
    to: { path: '/pos/products' },
    icon: { icon: 'tabler-shopping-bag' },
  },
  {
    title: 'Packed lunches',
    to: { path: '/pos/packed-lunches' },
    icon: { icon: 'tabler-backpack' },
  },
]

/** Rental screens (static fallback). Paths under /rental/... */
export const rentalNavigation: Array<NavLink> = [
  {
    title: 'Overview',
    to: { path: '/rental' },
    icon: { icon: 'tabler-layout-grid' },
  },
  {
    title: 'Rent out',
    to: { path: '/rental/rent-out' },
    icon: { icon: 'tabler-skiing' },
  },
  {
    title: 'Currently out',
    to: { path: '/rental/out' },
    icon: { icon: 'tabler-list-check' },
  },
  {
    title: 'Contracts',
    to: { path: '/rental/contracts' },
    icon: { icon: 'tabler-file-text' },
  },
  {
    title: 'Equipment types',
    to: { path: '/rental/types' },
    icon: { icon: 'tabler-category' },
  },
  {
    title: 'Items',
    to: { path: '/rental/items' },
    icon: { icon: 'tabler-barcode' },
  },
  {
    title: 'Season prices',
    to: { path: '/rental/prices' },
    icon: { icon: 'tabler-currency-euro' },
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
 * Static fallback navigation (Core + Hotel + Folio + POS + Rental).
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
    title: 'Folio',
    icon: { icon: 'tabler-receipt' },
    children: folioNavigation,
  },
  {
    title: 'POS',
    icon: { icon: 'tabler-tools-kitchen-2' },
    children: posNavigation,
  },
  {
    title: 'Rental',
    icon: { icon: 'tabler-skiing' },
    children: rentalNavigation,
  },
  {
    title: 'Profile',
    to: { path: '/user-profile' },
    icon: { icon: 'tabler-user-circle' },
  },
]
