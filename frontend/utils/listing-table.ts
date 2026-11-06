/**
 * Build EQMS DataTable config + cell-shaped rows from simple column defs.
 * Used when a dedicated listing config API is not available yet.
 */

export const buildDataTableConfig = (
  columns: Array<HotelColumn>,
  options: { defaultSortKey?: string, includeActions?: boolean } = {},
): DataTableConfig => {
  const includeActions = options.includeActions !== false

  const fields: Array<DataTableFieldConfig> = columns.map((column, index) => ({
    title: column.title,
    key: column.key,
    db_field: column.key,
    orderByField: column.key,
    includeAllFilter: false,
    default: true,
    disabled: index === 0,
    search_field: column.kind === 'bool' || column.kind === 'status' ? 'Dropdown' : 'Text',
    column_sort: true,
    sortable: true,
  }))

  if (includeActions) {
    fields.push({
      title: 'Actions',
      key: 'actions',
      db_field: '',
      orderByField: '',
      includeAllFilter: false,
      default: true,
      disabled: true,
    })
  }

  const defaultKey = options.defaultSortKey || columns[0]?.key || 'id'

  return {
    headers: {
      fields,
      default_sort: { key: defaultKey, order: 'asc' },
    },
    bulkActions: [],
    columnSearchable: {},
    showCheckbox: false,
    selectedColumns: [],
    advanceFilters: {},
    showExportButton: false,
    exportColumns: fields.filter(field => field.key !== 'actions'),
    externalFilters: {},
  }
}

export const mapRowToDataTableCells = (
  row: Record<string, any>,
  columns: Array<HotelColumn>,
  options: {
    linkKey?: string
    viewUrl?: (row: Record<string, any>) => string
    optionLabels?: Record<string, Array<HotelOption>>
  } = {},
) => {
  const id = Number(row.id)
  const mapped: Record<string, any> = {
    id,
    uuid: String(id),
    actions: {
      writePermission: true,
      viewPermission: Boolean(options.viewUrl),
      selectable: false,
      isLocked: false,
    },
  }

  columns.forEach((column) => {
    const raw = row[column.key]

    if (column.kind === 'bool') {
      const active = Boolean(raw)

      mapped[column.key] = {
        label: active ? 'Active' : 'Inactive',
        type: 'status',
        color: active ? 'success' : 'error',
      }

      return
    }

    if (column.kind === 'money') {
      mapped[column.key] = {
        label: formatCents(Number(raw ?? 0)),
        type: 'text',
      }

      return
    }

    if (column.kind === 'status') {
      mapped[column.key] = {
        label: raw == null || raw === '' ? '--' : String(raw),
        type: 'status',
        color: 'primary',
      }

      return
    }

    const optionsForKey = options.optionLabels?.[column.key]
    let label = raw == null || raw === '' ? '--' : String(raw)

    if (optionsForKey?.length) {
      const match = optionsForKey.find(option => String(option.value) === String(raw))

      label = match?.title ?? label
    }

    if (Array.isArray(raw))
      label = raw.length ? raw.join(', ') : '--'

    if (options.linkKey === column.key && options.viewUrl) {
      mapped[column.key] = {
        label,
        type: 'link',
        url: options.viewUrl(row),
      }

      return
    }

    mapped[column.key] = {
      label,
      type: 'text',
    }
  })

  return mapped
}
