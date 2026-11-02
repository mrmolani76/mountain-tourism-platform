export interface DashboardCategory {
  icon: string
  type: string
  color: 'primary' | 'error' | 'warning' | 'success' | 'info' | string
  title: string
  value: number
  isHover: boolean
}
