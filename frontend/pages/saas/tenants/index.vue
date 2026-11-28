<script setup lang="ts">
import type { SaasTenant } from '@/composables/api-clients/useSaasApiClient'

definePageMeta({
  layout: 'saas',
  title: 'Complexes',
})
useHead({ title: 'Complexes' })

const route = useRoute()
const router = useRouter()
const api = useSaasApiClient()
const loading = ref(true)
const initialLoading = ref(true)
const error = ref('')
interface SaasTableCell {
  type: 'text' | 'chip'
  label: string
  color?: string
  variant?: string
}

type SaasTenantRow = Omit<SaasTenant, 'name' | 'slug' | 'primary_domain' | 'created_at'> & {
  name: SaasTableCell
  slug: SaasTableCell
  primary_domain: SaasTableCell
  created_at: SaasTableCell
  status_label: SaasTableCell
}

const tenants = ref<SaasTenantRow[]>([])
const total = ref(0)
const tableParams = ref<Record<string, any>>({ page: 1, itemsPerPage: 10 })
const search = ref('')
const status = ref('all')

const parsePage = (value: unknown): number => {
  const page = Number(value)

  return Number.isInteger(page) && page > 0 ? page : 1
}

const tableConfig: DataTableConfig = {
  headers: {
    fields: [
      { title: 'Complex', key: 'name', db_field: 'name', orderByField: 'name', includeAllFilter: false, default: true, disabled: true, column_sort: true },
      { title: 'Slug', key: 'slug', db_field: 'slug', orderByField: 'slug', includeAllFilter: false, default: true, column_sort: true },
      { title: 'Domain', key: 'primary_domain', db_field: 'primary_domain', orderByField: 'primary_domain', includeAllFilter: false, default: true },
      { title: 'Status', key: 'status_label', db_field: 'is_active', orderByField: 'is_active', includeAllFilter: false, default: true },
      { title: 'Created', key: 'created_at', db_field: 'created_at', orderByField: 'created_at', includeAllFilter: false, default: true },
      { title: 'Actions', key: 'actions', db_field: 'id', orderByField: 'id', includeAllFilter: false, default: true, disabled: true },
    ],
    default_order: { field: 'name', dir: 'asc' },
  },
  bulkActions: [],
  columnSearchable: {},
  showCheckbox: false,
  selectedColumns: ['name', 'slug', 'primary_domain', 'status_label', 'created_at', 'actions'],
  advanceFilters: {},
  showExportButton: false,
  exportColumns: [],
  externalFilters: {},
}

const syncUrl = async (page: number) => {
  await router.replace({
    query: {
      search: search.value || undefined,
      status: status.value === 'all' ? undefined : status.value,
      page: page > 1 ? String(page) : undefined,
    },
  })
}

watch(
  () => [route.query.search, route.query.status, route.query.page],
  async ([routeSearch, routeStatus, routePage]) => {
    const nextSearch = typeof routeSearch === 'string' ? routeSearch : ''
    const nextStatus = ['active', 'inactive'].includes(String(routeStatus))
      ? String(routeStatus)
      : 'all'
    const nextPage = parsePage(routePage)

    if (
      nextSearch === search.value
      && nextStatus === status.value
      && nextPage === Number(tableParams.value.page || 1)
    ) {
      return
    }

    search.value = nextSearch
    status.value = nextStatus
    tableParams.value = { ...tableParams.value, page: nextPage }
    await loadItems(tableParams.value)
  },
)

const loadItems = async (params: Record<string, any> = tableParams.value) => {
  loading.value = true
  error.value = ''
  tableParams.value = params

  try {
    const page = parsePage(params.page)
    const pageSize = Math.min(100, Math.max(1, Number(params.itemsPerPage || 10)))
    const response = await api.getTenants({
      page: String(page),
      page_size: String(pageSize),
      search: search.value.trim(),
      status: status.value,
    })

    if (!response?.success) {
      tenants.value = []
      total.value = 0
      error.value = 'Could not load complexes. Retry the request.'
      return
    }

    tenants.value = response.data.map(tenant => ({
      ...tenant,
      name: { type: 'text', label: tenant.name },
      slug: { type: 'text', label: tenant.slug },
      primary_domain: {
        type: 'text',
        label: tenant.primary_domain || tenant.domains[0] || 'Not set',
      },
      created_at: {
        type: 'text',
        label: tenant.created_at ? new Date(tenant.created_at).toLocaleDateString() : '—',
      },
      status_label: {
        type: 'chip',
        label: tenant.is_active ? 'Active' : 'Inactive',
        color: tenant.is_active ? 'success' : 'warning',
        variant: 'tonal',
      },
    }))
    total.value = response.meta.total_count
    await syncUrl(page)
  }
  finally {
    loading.value = false
    initialLoading.value = false
  }
}

const applyFilters = async () => {
  tableParams.value = { ...tableParams.value, page: 1 }
  await loadItems(tableParams.value)
}

onMounted(async () => {
  search.value = typeof route.query.search === 'string' ? route.query.search : ''
  status.value = ['active', 'inactive'].includes(String(route.query.status))
    ? String(route.query.status)
    : 'all'
  tableParams.value.page = parsePage(route.query.page)
  await loadItems(tableParams.value)
})
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
      <div>
        <h1 class="text-h4 mb-1">
          Complexes
        </h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Manage tenant records and review their onboarding readiness.
        </p>
      </div>
      <VBtn
        to="/saas/tenants/create"
        prepend-icon="tabler-plus"
      >
        Add complex
      </VBtn>
    </div>

    <VSkeletonLoader
      v-if="initialLoading"
      type="table"
    />

    <VCard
      v-else
      variant="outlined"
    >
      <VCardText>
        <VRow
          align="center"
          class="mb-2"
        >
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="search"
              label="Search name or slug"
              prepend-inner-icon="tabler-search"
              clearable
              hide-details
              @keyup.enter="applyFilters"
              @click:clear="applyFilters"
            />
          </VCol>
          <VCol
            cols="12"
            sm="7"
            md="3"
          >
            <VSelect
              v-model="status"
              label="Status"
              :items="[
                { title: 'All statuses', value: 'all' },
                { title: 'Active', value: 'active' },
                { title: 'Inactive', value: 'inactive' },
              ]"
              hide-details
              @update:model-value="applyFilters"
            />
          </VCol>
          <VCol
            cols="12"
            sm="5"
            md="3"
            class="d-flex ga-2"
          >
            <VBtn
              variant="tonal"
              prepend-icon="tabler-filter"
              :loading="loading"
              @click="applyFilters"
            >
              Apply
            </VBtn>
            <VBtn
              variant="text"
              @click="search = ''; status = 'all'; applyFilters()"
            >
              Clear
            </VBtn>
          </VCol>
        </VRow>

        <VAlert
          v-if="error"
          type="error"
          variant="tonal"
          class="mb-4"
        >
          {{ error }}
          <template #append>
            <VBtn
              variant="text"
              @click="loadItems()"
            >
              Retry
            </VBtn>
          </template>
        </VAlert>

        <DataTable
          v-bind="tableConfig"
          v-model="tableParams"
          :list-data="tenants"
          :total-items="total"
          :loading="loading"
          :show-column-field="false"
          :show-add-button="false"
          :show-export-button="false"
          table-filter-identifier="saasTenantListing"
          @load-items="loadItems"
        >
          <template #actions="{ item }">
            <VBtn
              size="small"
              variant="tonal"
              :to="`/saas/tenants/${item.uuid}`"
              prepend-icon="tabler-eye"
            >
              View
            </VBtn>
          </template>
        </DataTable>
      </VCardText>
    </VCard>
  </div>
</template>
