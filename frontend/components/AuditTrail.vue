<script setup lang="ts">
const props = defineProps({
  module: {
    type: String,
    required: true,
  },
})

const { t } = useI18n()

// ---------------- Constants & Reactive Variables ----------------
const auditList = ref([])
const loading = ref(true)
const tableParams = ref({})
const tableHeaders = ref([])
const advanceFilters = ref([])
const searchColumn = ref([])
const pageLoading = ref(true)
const selectedColumns = ref([])
const url = ref()
const compareURL = ref()
const exportURL = ref()
const exportPdfURL = ref()
const exportColumns = ref([])
const identifier = ref()
const filterName = ref()
const pdfViewerDialog = ref(false)
const pdfUrl = ref()
const activitiesCompareDialog = ref(false);
const activitiesCompareDialogRef = ref();
const { getAuditListing, exportAuditTrailListing, saveAuditTrailTableFilters, getModuleWiseAuditTrailListConfig } = useAuditTrailApiClient()

// ---------------------- Methods ----------------------

const reloadListingData = async (params: ExportListingFormData) => {
  loading.value = true
  auditList.value = await getAuditListing(params)
  loading.value = false
}

const exportListing = async (exportParams: ExportListingFormData) => {
  const response = await exportAuditTrailListing(exportURL.value, exportParams)

  if (response)
    showAlertDialog(t('notification'), response.message, t('ok'))
}

const printListing = async (params: object) => {
  const apiBaseUrl = (useRuntimeConfig().public.apiBaseUrl) as string

  const exportUrl: any = new URL(`${apiBaseUrl}/backend/api/${exportPdfURL.value}`, window.location.origin)

  exportUrl.search = new URLSearchParams({ params: JSON.stringify(params) })

  pdfUrl.value = exportUrl.href
  pdfViewerDialog.value = true
}

const printDoc = () => {
  window.frames.iFramePrintViewer.focus()
  window.frames.iFramePrintViewer.print()
}

const columnFilterSave = async (selectedColumn: Array<string>) => {
  await saveAuditTrailTableFilters(selectedColumn, identifier.value, filterName.value)
}

const showCompareDialog = (id: number) => {
  const activityId = id;

  const params = {
    activityId,
  }

  activitiesCompareDialogRef.value.fetchAuditTrailCompare(params);
  activitiesCompareDialog.value = true;
}

// -------------------- Initial Code Execution --------------------
getModuleWiseAuditTrailListConfig({ module: props.module })
  .then(response => {
    tableHeaders.value = response.tableColumns
    advanceFilters.value = response.advanceFilters
    searchColumn.value = response.columnSearchable
    selectedColumns.value = response.selectedColumns
    identifier.value = response.identifier
    filterName.value = response.filterName
    url.value = response.url
    compareURL.value = response.compareURL
    exportURL.value = response.exportURL
    exportPdfURL.value = response.exportPdfURL
    exportColumns.value = response.exportColumns
    pageLoading.value = false
  })
</script>

<template>
  <div>
    <template v-if="pageLoading">
      <VSkeletonLoader
        :loading="pageLoading"
        type="table@1"
      />
    </template>
    <template v-else>
      <VCard class="d-flex flex-column">
        <!-- 👉 Header -->
        <VCardItem class="notification-section">
          <DataTable
            v-model="tableParams"
            :headers="tableHeaders"
            :listData="auditList.data"
            :totalItems="auditList.dataCount"
            :loading="loading"
            :showExportButton="!!exportURL"
            :showPrintButton="!!exportPdfURL"
            :advanceFilters="advanceFilters"
            :selectedColumns="selectedColumns"
            :tableFilterIdentifier="identifier"
            :columnSearchable="searchColumn"
            :exportColumns="exportColumns"
            @exportListing="exportListing"
            @loadItems="reloadListingData"
            @printListing="printListing"
            @columnFilterChange="columnFilterSave"
          >
            <template #actions="{ item }">
              <div class="d-flex gap-1">
                <VBtn
                  v-if="item.isCompare"
                  size="38"
                  color="primary"
                  @click="showCompareDialog(item.entity_activity_id)"
                >
                  <VTooltip activator="parent">
                    {{ $t('view') }}
                  </VTooltip>
                  <VIcon
                    size="22"
                    icon="tabler-eye"
                  />
                </VBtn>
              </div>
            </template>
          </DataTable>
        </VCardItem>
      </VCard>
    </template>

    <VDialog
      v-model="pdfViewerDialog"
      width="50%"
      persistent
      transition="dialog-transition"
    >
      <DialogCloseBtn @click="pdfViewerDialog = false" />

      <VCard class="mb-6">
        <VCardText>
          <iframe
            id="iFramePrintViewer"
            name="iFramePrintViewer"
            class="tw-w-full"
            style="block-size: 75vh;"
            :src="pdfUrl"
            frameborder="0"
            @load="printDoc"
          />
        </VCardText>
      </VCard>
    </VDialog>
  </div>
  <ActivitesCompare
    ref="activitiesCompareDialogRef"
    v-model:isDialogOpen="activitiesCompareDialog"
    :compareURL="compareURL"
    :activityId="activityId"
    :entityId="false"
  />
</template>
