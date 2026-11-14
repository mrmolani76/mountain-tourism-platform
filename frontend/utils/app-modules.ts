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
 * MVP focus: Core + Hotel + Folio + POS + Rental + Services.
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
  {
    key: 'services',
    title: 'Services',
    icon: 'tabler-spa',
    description: 'Spa, sports and activity slots, bookings and outings.',
    endpoint: '/api/services/',
    features: ['Catalog and slots', 'Bookings', 'Guided outings'],
  },
  {
    key: 'transport',
    title: 'Transport',
    icon: 'tabler-bus',
    description: 'Fleet trips, pickups, lift status and pass sales.',
    endpoint: '/api/transport/',
    features: ['Trips and passenger lists', 'Lift status', 'Lift pass sell'],
  },
  {
    key: 'weather',
    title: 'Weather',
    icon: 'tabler-cloud',
    description: 'Points, limits, warnings and cancel-for-weather.',
    endpoint: '/api/weather/',
    features: ['MET fetch', 'Limits and warnings', 'Weather cancel voids'],
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

/** Services screens (static fallback). Paths under /services/... */
export const servicesNavigation: Array<NavLink> = [
  {
    title: 'Overview',
    to: { path: '/services' },
    icon: { icon: 'tabler-layout-grid' },
  },
  {
    title: 'Bookings',
    to: { path: '/services/bookings' },
    icon: { icon: 'tabler-calendar-plus' },
  },
  {
    title: 'Slots',
    to: { path: '/services/slots' },
    icon: { icon: 'tabler-clock' },
  },
  {
    title: 'Outings',
    to: { path: '/services/outings' },
    icon: { icon: 'tabler-trekking' },
  },
  {
    title: 'Catalog',
    to: { path: '/services/catalog' },
    icon: { icon: 'tabler-list' },
  },
  {
    title: 'Schedule rules',
    to: { path: '/services/schedule-rules' },
    icon: { icon: 'tabler-calendar-repeat' },
  },
]

/** Transport screens (static fallback). Paths under /transport/... */
export const transportNavigation: Array<NavLink> = [
  { title: 'Overview', to: { path: '/transport' }, icon: { icon: 'tabler-layout-grid' } },
  { title: 'Trips', to: { path: '/transport/trips' }, icon: { icon: 'tabler-bus' } },
  { title: 'Pickup', to: { path: '/transport/pickup' }, icon: { icon: 'tabler-train' } },
  { title: 'Vehicles', to: { path: '/transport/vehicles' }, icon: { icon: 'tabler-car' } },
  { title: 'Drivers', to: { path: '/transport/drivers' }, icon: { icon: 'tabler-user' } },
  { title: 'Routes', to: { path: '/transport/routes' }, icon: { icon: 'tabler-route' } },
  { title: 'Lifts', to: { path: '/transport/lifts' }, icon: { icon: 'tabler-building-bridge' } },
  { title: 'Lobby board', to: { path: '/transport/lobby' }, icon: { icon: 'tabler-device-tv' } },
  { title: 'Lift passes', to: { path: '/transport/passes' }, icon: { icon: 'tabler-ticket' } },
]

/** Weather screens (static fallback). Paths under /weather/... */
export const weatherNavigation: Array<NavLink> = [
  { title: 'Overview', to: { path: '/weather' }, icon: { icon: 'tabler-layout-grid' } },
  { title: 'Points', to: { path: '/weather/points' }, icon: { icon: 'tabler-map-pin' } },
  { title: 'Limits', to: { path: '/weather/limits' }, icon: { icon: 'tabler-ruler' } },
  { title: 'Warnings', to: { path: '/weather/warnings' }, icon: { icon: 'tabler-alert-triangle' } },
  { title: 'Avalanche', to: { path: '/weather/avalanche' }, icon: { icon: 'tabler-mountain' } },
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
 * Static fallback navigation (Core + Hotel + Folio + POS + Rental + Services + Transport + Weather).
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
    title: 'Services',
    icon: { icon: 'tabler-spa' },
    children: servicesNavigation,
  },
  {
    title: 'Transport',
    icon: { icon: 'tabler-bus' },
    children: transportNavigation,
  },
  {
    title: 'Weather',
    icon: { icon: 'tabler-cloud' },
    children: weatherNavigation,
  },
  {
    title: 'Profile',
    to: { path: '/user-profile' },
    icon: { icon: 'tabler-user-circle' },
  },
]
