export interface AppTab {
  icon: string
  tab: string
  title: string
  module?: string
  submodule?: string
  uuid?: string
  entityId?: string
  identifier?: string
  filterName?: string
  pinnedTab?: boolean
  multiple?: true
  component: string | Component
  url?: string
  entityRandomId?: any
  scope?: string
  selectedVersionId?: any
  selectedVersion?: any
  versions?: Array<object>
  imgIcon?: string
  count?: number | string
  expandable?: boolean
  expandData?: Array<{ title: string; tab: string; count?: number | string }>
}
