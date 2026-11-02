<script setup lang="ts">
const props = defineProps({
  listData: { type: Array, default() { return [] } },
  totalItems: { type: Number, default: 0 },

  loading: { type: Boolean, default: false },
  showAddButton: { type: Boolean, default: false },
  showColumnField: { type: Boolean, default: true },
  showExportButton: { type: Boolean, default: false },
  showPrintButton: { type: Boolean, default: false },
  resetColumnFilter: { type: Boolean, default: true },

  checkboxIdKey: { type: String, default: 'id' },
  selectedColumns: { type: Array<string>, default() { return [] } },
  headers: { type: Object as PropType<DataTableHeaderConfig>, default() { return {} } },
  advanceFilters: { type: Object as PropType<Record<string, any>>, default() { return {} } },
  bulkActions: { type: Array<string>, default() { return [] } },
  tableFilterIdentifier: { type: String },
  breadcrumbs: { type: Array, default() { return [] }, required: false },
  modelValue: { type: Object },
  height: { type: Number, default: null, required: false },
  fixedHeader: { type: String, default: '', required: false },
  selectedItemIds: { type: Array, default() { return [] } },
  columnSearchable: { type: Object as PropType<Record<string, Array<OptionItem>>>, default() { return {} } },
  externalFilters: { type: Object as PropType<ExternalFilters>, default() { return {} } },
  exportColumns: { type: Array<DataTableFieldConfig>, default() { return [] } },
  addNewButtonLabel: { type: String, default: 'addNew' },
  size: { type: String, default: 'default' },
})

const emits = defineEmits<{
  (e: 'addNew'): void
  (e: 'loadItems', params: any): void
  (e: 'exportListing', exportParams: any): void
  (e: 'columnFilterChange', payload: Record<string, any>): void
  (e: 'bulkActionTrigger', action: string): void
  (e: 'getNestedListData', breadcrumb: any, index: number): void
  (e: 'update:modelValue', payload: Record<string, any>): void
  (e: 'update:selectedItemIds', ids: any[]): void
  (e: 'printListing', exportParams: any): void
  (e: 'totalCounts', counts: number): void
  (e: 'filterToBeSavedParam', param: Record<string, any>): void
}>()

const { getDatatableListingConfig } = useSharedApiClient()

const nuxtApp = useNuxtApp()
const { t } = useI18n()

const itemsPerPage = ref(10)
const page = ref(1)
const dataTableServerHeaders = ref([] as Array<DataTableFieldConfig>)
const tempColumnSearchable = ref(props.columnSearchable || [])
const exportDialogToggle = ref(false)
const columnFilterSelected = ref()
const tableParams = ref()
const showFilters = ref(false)
const selectedBulkOption = ref()
const selectedItemIdsArr = ref(props.selectedItemIds)
const applyFilterBtnDisabled = ref(true)
const ascActiveIcon = ref()
const descActiveIcon = ref()
const sortBy = ref(props.headers.default_sort)
const filteredItems = ref({} as { columnSearch: { [key: string]: any } })
const searchColumn = ref({} as { [key: string]: any })
const externalFilterModel = ref({} as { [key: string]: any })
const savePreferenceLoadFlag = ref(false)
const disabledExternalFilterModel = ref(false)
const countries = ref(countryOptions)
const columnSortable = ref({});
const columnsToGetSearchable = ['users']
const allHeaders = (props.headers.fields) ? props.headers.fields : []
let filterToBeSavedParam: { [key: string]: any } = {}
let timer: ReturnType<typeof setTimeout>;

const paginationMeta = computed(() => {
  if (props.totalItems > 0) {
    const start = (tableParams.value.page - 1) * tableParams.value.itemsPerPage + 1

    const end = Math.min(tableParams.value.page * tableParams.value.itemsPerPage, props.totalItems)

    return t('showingXEntries', { from: (props.totalItems === 0 ? 0 : start), to: end, total: props.totalItems })
  }
  else {
    return t('showingXEntries', { from: 0, to: 0, total: 0 })
  }
})

const checkColumnSelectionDirty = computed(() => {
  const tempHeaders = dataTableServerHeaders.value.map(col => col.key)

  const diff1 = columnFilterSelected.value.filter((value: string) => !tempHeaders.includes(value))
  const diff2 = tempHeaders.filter(value => !columnFilterSelected.value.includes(value))

  return diff1.length > 0 || diff2.length > 0
})

const checkColumnValueExist = computed(() => {
  let check = true
  if (filteredItems.value.columnSearch) {
    for (const filter of columnFilterSelected.value) {
      if (filteredItems.value.columnSearch[filter]) {
        check = false
        break
      }
    }
  }

  return check
})

const tooltipMessage = computed(() => {
  return checkColumnValueExist.value ? t('noFilterToClear') : t('clearAllFilter')
})

const statesArr = computed(() => {
  let countryCode = []

  if (!searchColumn.value.country?.length)
    return [];

  countryCode = searchColumn.value.country

  return countryOptions.filter(country => countryCode.some((code: string) => country.value === countryCode[code]))
    .flatMap((country: any) => country.states).map(state => {
      return {
        title: state.name,
        value: state.state_code,
      }
    });
})

const colWidth = computed(() => {
  switch (props.size) {
    case 'small':
      return 'min-width: 6rem !important; max-width:14rem !important'
    case 'default':
      return 'min-width: 12rem !important; max-width:20rem !important'
    case 'large':
      return 'min-width: 18rem !important; max-width:26rem !important'
  }
})

const activeFilters = computed(() => {
  let activeFilterCount = 0
  for (const filter in props.externalFilters) {
    if (externalFilterModel.value[filter]?.length) {
      disabledExternalFilterModel.value = false
      activeFilterCount++
    }
    else {
      disabledExternalFilterModel.value = true
    }
  }

  return activeFilterCount
})

const loadColumnSearchable = async (columnKeys: Array<ColumnListing>) => {
  if (!columnKeys.length)
    return;

  const data = await getDatatableListingConfig({ holders: columnKeys })
  if (data) {
    for (const columnKey in data)
      tempColumnSearchable.value[columnKey] = data[columnKey]
  }
}

const handleColumnFilterChange = () => {
  emits('columnFilterChange', {
    ...filterToBeSavedParam,
    columnFilter: [...columnFilterSelected.value],
  })
}

const resetFilter = () => {
  const tempHeaders = []
  for (const col of allHeaders) {
    col.sortable = false
    if (col.disabled || col.default)
      tempHeaders.push(col)
  }
  clearAllFilter()
  dataTableServerHeaders.value = tempHeaders
  columnFilterSelected.value = tempHeaders.map(col => col.key)
  handleColumnFilterChange()
}

const filterColumns = () => {
  if (columnFilterSelected.value.length > 20) {
    nuxtApp.$toast.error(t('columnCannotExceed20'))

    return;
  }

  const tempHeaders = []

  const loadColumnSearchableAll: Array<ColumnListing> = []
  for (const col of allHeaders) {
    if (columnFilterSelected.value.includes(col.key)) {
      const fetchDataFrom = col.key
      if (columnsToGetSearchable.includes(fetchDataFrom))
        loadColumnSearchableAll.push({ key: col.key, value: fetchDataFrom, includeAllFilter: col.includeAllFilter || true })

      tempHeaders.push(col)
      if (searchColumn.value[col.key] && filteredItems.value.columnSearch[col.key] !== searchColumn.value[col.key])
        filteredItems.value.columnSearch[col.key] = searchColumn.value[col.key]
    }
    else if (searchColumn.value[col.key]) {
      delete filteredItems.value.columnSearch[col.key]
    }
  }

  loadColumnSearchable(loadColumnSearchableAll)

  dataTableServerHeaders.value = tempHeaders

  handleColumnFilterChange()
  loadItems()
  updateModelValue()
}

const exportListing = async (formData: Record<string, any>) => {
  let exportParams: ExportListingFormData = {
    exportFormat: formData.exportFormat,
    columns: formData.columns,
  }

  if (formData.applyFilters) {
    exportParams = {
      ...exportParams,
      ...tableParams.value,
      ...filteredItems.value,
      externalFilterData: {
        ...externalFilterModel.value,
      },
    }
  }

  emits('exportListing', exportParams)
}

const setUserPrefrenceFlag = () => {
  savePreferenceLoadFlag.value = true
}

const saveAndApplyFilter = async () => {
  handleColumnFilterChange()
  filterToBeSavedParam = {}
  applyFilterBtnDisabled.value = true
  emits('filterToBeSavedParam', filterToBeSavedParam)
}

const filterUpdated = (param: string, value: Array<string | number> | string | null, flagClearUserPrefrenceCall = true) => {
  filterToBeSavedParam[param] = value
  if (savePreferenceLoadFlag.value && flagClearUserPrefrenceCall)
    saveAndApplyFilter()
}

const externalFilterUpdated = (param: string, value: Array<string | number> | string) => {
  applyFilterBtnDisabled.value = false
  filterToBeSavedParam[param] = value
}

const updateModelValue = () => {
  const params = {
    ...tableParams.value,
    ...filteredItems.value,
    columnFilterSelected: columnFilterSelected.value,
    externalFilterData: {
      ...externalFilterModel.value,
    },
  }

  emits('update:modelValue', params)
}

const loadItems = async () => {
  if (!searchColumn.value.country?.length && searchColumn.value.state?.length) {
    searchColumn.value.state = []
    filteredItems.value.columnSearch.state = []
    filterUpdated('state', '', true)
  }
  tableParams.value.sortBy = columnSortable.value;

  const params = {
    ...tableParams.value,
    ...filteredItems.value,
    columnFilterSelected: columnFilterSelected.value,
    externalFilterData: {
      ...externalFilterModel.value,
    },
  }

  emits('loadItems', params)
  updateModelValue()
}

const saveFilterAndSearch = async (searchValue: any, searchC: string) => {
  if (!filteredItems.value?.columnSearch)
    filteredItems.value.columnSearch = {}

  filteredItems.value.columnSearch[searchC] = searchValue;

  if (timer)
    clearTimeout(timer)

  timer = setTimeout(() => {
    filterUpdated(searchC, searchValue)
    loadItems()
  }, 500)
}

const sorting = (columnKey: string, columnOrder: 'asc' | 'desc') => {
  const isAsc = columnOrder === 'asc'
  const activeIcon = isAsc ? ascActiveIcon : descActiveIcon
  const inactiveIcon = isAsc ? descActiveIcon : ascActiveIcon

  if (activeIcon.value !== columnKey) {
    activeIcon.value = columnKey
    inactiveIcon.value = null
  }
  else {
    activeIcon.value = null
  }

  if (activeIcon.value != null) {
    tableParams.value.sortBy
      = {
        key: columnKey,
        order: columnOrder,
      }
  }
  else {
    delete tableParams.value.sortBy
  }
  columnSortable.value = tableParams.value.sortBy;

  const params = {
    ...filteredItems.value,
    ...tableParams.value,
    ...filteredItems.value,
    columnFilterSelected: columnFilterSelected.value,
  }

  emits('loadItems', params)
  updateModelValue()
}

const clearExternalFilters = () => {
  for (const filter in props.externalFilters) {
    filterToBeSavedParam[filter] = null
    delete externalFilterModel.value[filter]
  }
  saveAndApplyFilter()
  loadItems()
  showFilters.value = false
}

const clearAllFilter = () => {
  filteredItems.value.columnSearch = {}
  for (const filter of columnFilterSelected.value) {
    searchColumn.value[filter] = undefined
    filterUpdated(filter, null, false)
  }
  loadItems()
  saveAndApplyFilter()
}

const printListing = async () => {
  const exportParams = {
    columnFilterSelected: columnFilterSelected.value,
    ...tableParams.value,
    ...filteredItems.value,
    externalFilterData: {
      ...externalFilterModel.value,
    },
  }

  emits('printListing', exportParams)
}

const resetBulkOptions = () => {
  selectedBulkOption.value = null
}

const prepareInitialData = () => {
  const loadColumnSearchableAll: Array<ColumnListing> = []

  const tempHeaders = []

  for (const col of allHeaders) {
    col.sortable = false

    const fetchDataFrom = col.key
    if (columnsToGetSearchable.includes(fetchDataFrom))
      loadColumnSearchableAll.push({ key: col.key, value: fetchDataFrom, includeAllFilter: col.includeAllFilter || true })

    // if no selectedColumns
    if (!props.selectedColumns.length && (col.disabled || col.default))
      tempHeaders.push(col)

    // if there is selectedColumns
    if (props.selectedColumns.length && (props.selectedColumns.includes(col.key) || col.disabled))
      tempHeaders.push(col)
  }

  loadColumnSearchable(loadColumnSearchableAll)

  dataTableServerHeaders.value = tempHeaders
  columnFilterSelected.value = tempHeaders.map(col => col.key)

  if (props.advanceFilters) {
    for (const filter in props.advanceFilters) {
      if (!props.advanceFilters[filter])
        continue;

      searchColumn.value[filter] = props.advanceFilters[filter]

      if (!filteredItems.value?.columnSearch)
        filteredItems.value.columnSearch = {};

      filteredItems.value.columnSearch[filter] = props.advanceFilters[filter]
    }
  }

  if (props.externalFilters) {
    for (const filter in props.externalFilters)
      externalFilterModel.value[filter] = props.externalFilters[filter].selected
  }
}

watch(() => props.selectedItemIds, () => {
  selectedItemIdsArr.value = props.selectedItemIds
})

defineExpose({ resetBulkOptions })

prepareInitialData()
</script>

<template>
  <div>
    <div>
      <div class="tw-grid tw-grid-cols-8 tw-justify-end tw-gap-3 tw-mb-4 pt-1">
        <div
          v-if="showColumnField"
          class="tw-col-span-2 tw-flex"
        >
          <VSelect
            v-model="columnFilterSelected"
            :items="allHeaders"
            :itemTitle="item => item.title"
            :itemValue="item => item.key"
            :label="$t('columns')"
            multiple
          >
            <template #selection="{ index }">
              <span
                v-if="index === 0"
                class="text-caption align-self-center"
              >
                {{ $t('xColumnsSelected', { x: columnFilterSelected.length }) }}
              </span>
            </template>
            <template #item="{ props, item }">
              <template v-if="item.raw.header">
                <VListItem
                  v-bind="props"
                  disabled
                  class="tw-font-bold tw-opacity-100 tw-rounded-none tw-p-0 tw-m-0"
                  variant="tonal"
                />
              </template>
              <VTooltip
                v-else-if="item.raw.key === 'actions'"
                :text="$t('actionsColumnHidden')"
              >
                <template #activator="{ props }">
                  <div v-bind="props">
                    <VListItem
                      v-bind="props1"
                      :value="item.raw.key"
                      :disabled="item.raw.disabled"
                      width="17.8rem"
                    >
                      {{
                        item.raw.title }}
                    </VListItem>
                  </div>
                </template>
              </VTooltip>
              <VListItem
                v-else
                v-bind="props"
                :disabled="item.raw.disabled"
                width="17.8rem"
              />
            </template>
          </VSelect>
          <VBtn
            v-if="checkColumnSelectionDirty"
            variant="tonal"
            color="default"
            icon="tabler-check"
            density="compact"
            class="tw-mx-1 tw-ms-16 tw-mt-1"
            @click="filterColumns"
          />
        </div>
        <VBtn
          v-if="showColumnField"
          prependIcon="tabler-filter-off"
          class="tw-col-1 tw-text-center tw-px-14 tw-h-full lg:tw-w-32 xl:tw-w-full"
          @click="resetFilter"
        >
          <span class="tw-h-full tw-text-sm">
            {{ $t('resetFilter') }}
          </span>
        </VBtn>

        <VSelect
          class=" md:tw-w-28 md:tw-ms-14 lg:tw-ms-14 xl:tw-w-full xl:tw-ms-0"
          :label="$t('perPage')"
          :modelValue="itemsPerPage"
          :items="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
          ]"
          @update:modelValue="itemsPerPage = parseInt($event, 10),
                              page = 1"
        />

        <VBtn
          v-if="Object.keys(externalFilters)?.length"
          variant="outlined"
          color="primary"
          class="tw-h-full md:tw-h-full tw-text-sm tw-mx-0 xl:tw-mx-0 xl:tw-ml-0 md:tw-ml-28 tw-px-16"
          prependIcon="tabler-filter"
          @click="showFilters = !showFilters"
        >
          <template #default>
            <span class="">
              <template v-if="activeFilters">
                {{ activeFilters }}&nbsp;{{ $t('active_filters') }}
              </template>
              <template v-else>
                {{ $t('see_filters') }}
              </template>
            </span>
          </template>
        </VBtn>
        <VBtn
          v-if="showAddButton"
          prependIcon="tabler-plus"
          class="tw-col-1 tw-text-center tw-col-end-11"
          @click="emits('addNew')"
        >
          {{ $t(addNewButtonLabel) }}
        </VBtn>

        <VBtn
          v-if="showExportButton"
          prependIcon="tabler-table-export"
          class="tw-col-1 tw-text-center tw-col-end-12"
          :disabled="!listData?.length"
          @click="exportDialogToggle = !exportDialogToggle"
        >
          {{ $t('export') }}
        </VBtn>

        <VTooltip :text="$t('print')">
          <template #activator="{ props: prps }">
            <VBtn
              v-if="showPrintButton"
              class="tw-col-1 tw-text-center tw-col-end-13"
              :disabled="!listData?.length"
              v-bind="prps"
              @click="printListing"
            >
              <VIcon>tabler-printer</VIcon>
            </VBtn>
          </template>
        </VTooltip>
      </div>
      <div
        v-show="showFilters"
        class="tw-mb-4"
      >
        <div class="tw-grid tw-grid-cols-8 tw-gap-3">
          <div v-for="(externalFilter, index) in externalFilters">
            <DataTableExternalFilters
              v-model="externalFilterModel[index]"
              :externalFilter="externalFilter"
              :index="index"
              @update:modelValue="externalFilterUpdated(index, $event);"
              @change:modelValue="setUserPrefrenceFlag"
            />
          </div>

          <div>
            <VTooltip :text="$t('apply_filters')">
              <template #activator="{ props }">
                <VBtn
                  color="primary"
                  density="compact"
                  class="tw-mx-1 tw-mt-1"
                  icon="tabler-check"
                  v-bind="props"
                  :disabled="applyFilterBtnDisabled"
                  @click="loadItems(); saveAndApplyFilter();"
                />
              </template>
            </VTooltip>
            <VTooltip :text="$t('clear_all_filters')">
              <template #activator="{ props }">
                <VBtn
                  variant="tonal"
                  color="primary"
                  density="compact"
                  class="tw-mx-1 tw-mt-1"
                  icon="tabler-x"
                  v-bind="props"
                  :disabled="disabledExternalFilterModel"
                  @click="clearExternalFilters"
                />
              </template>
            </VTooltip>
          </div>
        </div>
      </div>
      <VBreadcrumbs
        v-if="breadcrumbs"
        class="px-0 py-2"
      >
        <VBreadcrumbsItem v-for="(breadcrumb, index) in breadcrumbs">
          <span
            v-if="breadcrumb.parent_id === ''"
            class="cursor-pointer"
            @click="selectedItemIdsArr = []; emits('update:selectedItemIds', selectedItemIdsArr); loadItems()"
          >{{
            breadcrumb.title
          }}
          </span>
          <span
            v-else
            class="cursor-pointer"
            @click="selectedItemIdsArr = []; emits('update:selectedItemIds', selectedItemIdsArr); emits('getNestedListData', breadcrumb, index)"
          >{{
            breadcrumb.title
          }}</span>
          <span v-if="breadcrumbs.length !== index + 1">
            <VIcon
              class="tw-ml-2"
              size="18"
            >tabler-chevrons-right</VIcon>
          </span>
        </VBreadcrumbsItem>
      </VBreadcrumbs>
      <VDataTableServer
        v-model:itemsPerPage="itemsPerPage"
        v-model:options="tableParams"
        v-model="selectedItemIdsArr"
        v-model:page="page"
        :headers="dataTableServerHeaders"
        :itemsLength="totalItems"
        :items="listData"
        :sortBy="sortBy"
        :loading="loading"
        class="elevation-1"
        :showSelect="!!bulkActions.length"
        :itemValue="item => `${item[checkboxIdKey]}`"
        itemSelectable="actions.selectable"
        :height="height"
        fixedHeader
        @update:options="loadItems"
        @update:modelValue="emits('update:selectedItemIds', selectedItemIdsArr)"
      >
        <template
          v-for="(column) in dataTableServerHeaders"
          :key="column.key"
          #[`header.${column.key}`]
        >
          <div
            class="tw-my-2"
            :style="colWidth"
          >
            <div class="tw-flex tw-mb-2 tw-w-full">
              <div class="tw-w-full">
                <div>
                  {{ column.title }}
                </div>
                <div class="tw-grow">
                  <div v-if="column.key === 'actions' && resetColumnFilter">
                    <VTooltip
                      :text="tooltipMessage"
                      class="tw-whitespace-nowrap"
                    >
                      <template #activator="{ props }">
                        <div
                          v-bind="props"
                          class="tw-inline-flex tw-items-center, tw-justify-center"
                        >
                          <VBtn
                            size="38"
                            color="primary"
                            variant="outlined"
                            :disabled="checkColumnValueExist"
                            @click="clearAllFilter"
                          >
                            <VIcon
                              size="22"
                              icon="tabler-x"
                            />
                          </VBtn>
                        </div>
                      </template>
                    </VTooltip>
                  </div>
                  <div v-if="column.key === 'country'">
                    <AutoComplete
                      v-model="searchColumn[column.key]"
                      :items="countries"
                      class="tw-normal-case"
                      multiple
                      itemTitle="title"
                      itemValue="value"
                      clearable
                      maxShowSelected="1"
                      @update:modelValue="saveFilterAndSearch(searchColumn[column.key], column.key); setUserPrefrenceFlag(); searchColumn.state = []; filteredItems.columnSearch.state = []"
                    />
                  </div>
                  <div v-if="column.key === 'state'">
                    <AutoComplete
                      v-if="searchColumn.country?.length"
                      v-model="searchColumn[column.key]"
                      :items="statesArr"
                      class="tw-normal-case"
                      itemTitle="title"
                      itemValue="value"
                      multiple
                      clearable
                      maxShowSelected="1"
                      :disabled="!!statesArr.length"
                      @update:modelValue="saveFilterAndSearch(searchColumn[column.key], column.key); setUserPrefrenceFlag()"
                    />
                  </div>
                  <div v-else>
                    <SearchField
                      v-model="searchColumn[column.key]"
                      :holders="column.key"
                      :fieldData="tempColumnSearchable[column.key]"
                      :column="column.search_field"
                      :columnKey="column.key"
                      @update:modelValue="saveFilterAndSearch(searchColumn[column.key], column.key)"
                      @change:modelValue="setUserPrefrenceFlag"
                    />
                  </div>
                </div>
              </div>
              <div
                v-if="column.column_sort"
                class="tw-ml-2 tw-grid tw-grid-rows-2"
              >
                <VIcon
                  icon="tabler-arrow-up"
                  :class="ascActiveIcon === column.key ? 'tw-opacity-100' : 'tw-opacity-30'"
                  size="20"
                  @click="sorting(column.key, 'asc')"
                />
                <VIcon
                  icon="tabler-arrow-down"
                  :class="descActiveIcon === column.key ? 'tw-opacity-100' : 'tw-opacity-30'"
                  size="20"
                  @click="sorting(column.key, 'desc')"
                />
              </div>
            </div>
          </div>
        </template>

        <template
          v-for="(_, name) in $slots"
          #[name]="slotProps"
        >
          <slot
            :name="name"
            v-bind="slotProps || {}"
          />
        </template>

        <template
          v-for="(column) in dataTableServerHeaders"
          #[`item.${column.key}`]="{ item }"
        >
          <slot
            v-if="column.key === 'actions'"
            name="actions"
            :item="item"
          />
          <DataTableCell
            v-else-if="item[column.key]"
            :key="column.key"
            :itemData="item[column.key]"
            @getNestedListData="selectedItemIdsArr = []; emits('update:selectedItemIds', selectedItemIdsArr); emits('getNestedListData', item, 0)"
            @totalCounts="val => emits('totalCounts', val)"
          />
        </template>
        <template #bottom>
          <VDivider />

          <div :class="`tw-grid ${(bulkActions.length) ? 'tw-grid-cols-3' : 'tw-grid-cols-2'} tw-justify-between tw-gap-4 tw-p-4 pb-6`">
            <div
              v-if="bulkActions.length"
              class="tw-flex tw-gap-2"
            >
              <VSelect
                v-model="selectedBulkOption"
                :items="bulkActions.map((val) => {
                  return {
                    title: t(val),
                    value: val,
                  }
                })"
                :label="$t('bulkActions')"
                :placeholder="$t('selectItem')"
              />
              <div>
                <VTooltip
                  v-if="!selectedItemIdsArr.length"
                  activator="parent"
                >
                  {{ $t('selectAtLeastOneItemToExecuteBulkOperation') }}
                </VTooltip>
                <VTooltip
                  v-else-if="!selectedBulkOption"
                  activator="parent"
                >
                  {{ $t('selectBulkOption') }}
                </VTooltip>
                <VBtn
                  class="tw-col-1 tw-text-center"
                  :disabled="!(selectedBulkOption && selectedItemIdsArr.length > 0)"
                  @click="emits('bulkActionTrigger', selectedBulkOption)"
                >
                  {{ $t('apply') }}
                </VBtn>
              </div>
            </div>
            <div class="text-disabled tw-text-center tw-mt-1">
              {{ paginationMeta }}
            </div>

            <VPagination
              v-model="page"
              :length="Math.ceil(totalItems / itemsPerPage)"
              :totalVisible="$vuetify.display.xs ? 1 : 5"
            >
              <template #prev="slotProps">
                <VBtn
                  variant="tonal"
                  color="default"
                  v-bind="slotProps"
                  :icon="false"
                >
                  {{ $t('previous') }}
                </VBtn>
              </template>

              <template #next="slotProps">
                <VBtn
                  variant="tonal"
                  color="default"
                  v-bind="slotProps"
                  :icon="false"
                >
                  {{ $t('next') }}
                </VBtn>
              </template>
            </VPagination>
          </div>
        </template>
      </VDataTableServer>
    </div>
    <div>
      <DataTableExport
        :dialog="exportDialogToggle"
        :exportColumns="exportColumns"
        :showColumnField="showColumnField"
        :currentColumns="columnFilterSelected"
        @updatedDialogValue="value => { exportDialogToggle = value }"
        @submitExportListingForm="exportListing"
      />
    </div>
  </div>
</template>
