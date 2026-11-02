export interface DataTableConfig {
  headers: DataTableHeaderConfig
  bulkActions: Array<string>
  columnSearchable: Record<string, Array<OptionItem>>
  showCheckbox: boolean
  selectedColumns: Array<string>
  advanceFilters: Record<string, any>
  showExportButton: boolean
  exportColumns: Array<DataTableFieldConfig>
  externalFilters: ExternalFilters
}

export interface DataTableHeaderConfig {
  fields: Array<DataTableFieldConfig>
  default_sort: SortConfig
}

export interface DataTableFieldConfig {
  title: string
  key: string
  db_field: string
  orderByField: string
  includeAllFilter: boolean
  disabled?: boolean
  search_field?: 'Text' | 'Number' | 'Date' | string
  column_sort?: boolean

  // for VDataTableServer component
  sortable?: boolean
  default?: boolean
}

export interface SortConfig {
  key: string
  order: 'asc' | 'desc'
}

export interface OptionItem {
  value: number | string
  title: string
  [key: string]: any
}

export interface ExternalFilters {
  [key: string]: ExternalFiltersType
}

export interface ExternalFiltersType {
  selected: string
  label: string
  items: Array<OptionItem>
  param: string
  multiple?: boolean
}

export interface DataTableResult<T> extends ApiResponse<Array<T>> {
  pagination: Pagination
}

export interface Pagination {
  total: number
}

export interface ColumnListing {
  key: string
  value: any
  includeAllFilter: boolean
  module?: string
  submodule?: string
  identifier?: string
  filterName?: string
}
