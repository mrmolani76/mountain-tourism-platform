<script setup lang="ts">
/**
 * Folio listing — EQMS DataTable (list/config + list).
 * Money actions stay on folio detail (charge / pay / void).
 */

definePageMeta({ navActiveLink: 'folios' })
useHead({ title: 'Folios' })

const client = useFolioApiClient()

const configLoading = ref(true)
const listLoading = ref(false)
const tableConfig = ref<DataTableConfig | null>(null)
const listResult = ref<DataTableResult<any>>({
  status: false,
  data: [],
  pagination: { total: 0 },
})
const tableParams = ref<Record<string, any>>({})

const loadConfig = async () => {
  configLoading.value = true
  try {
    const result = await client.listConfig()
    if (result?.status)
      tableConfig.value = result.data as DataTableConfig
  }
  finally {
    configLoading.value = false
  }
}

const reloadListingData = async (params: any = tableParams.value) => {
  listLoading.value = true
  tableParams.value = params || {}
  try {
    const result = await client.listRows(params || {})
    if (result?.status)
      listResult.value = result
  }
  finally {
    listLoading.value = false
  }
}

onMounted(loadConfig)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <h2 class="text-h5 mb-1">
          Guest folios
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Open bills, balance, charge to room, payments and voids.
        </p>
      </div>
      <VBtn
        color="primary"
        variant="tonal"
        prepend-icon="tabler-door"
        :to="{ path: '/folios/charge-to-room' }"
      >
        Charge to room
      </VBtn>
    </div>

    <template v-if="configLoading || !tableConfig">
      <VSkeletonLoader
        :loading="configLoading"
        type="table"
      />
    </template>

    <DataTable
      v-else
      v-bind="tableConfig"
      v-model="tableParams"
      :list-data="listResult.data"
      :total-items="listResult.pagination?.total ?? 0"
      :loading="listLoading"
      :show-add-button="false"
      checkbox-id-key="id"
      @load-items="reloadListingData"
    >
      <template #actions="{ item }">
        <VBtn
          size="38"
          color="primary"
          :to="`/folios/view/${item.id}`"
        >
          <VTooltip activator="parent">
            Open
          </VTooltip>
          <VIcon
            icon="tabler-eye"
            size="22"
          />
        </VBtn>
      </template>
    </DataTable>
  </div>
</template>
