<script setup lang="ts">
const props = defineProps({
  entityId: {
    type: String,
    required: true,
  },
  module: {
    type: String,
    required: true,
  },
  submodule: {
    type: String,
    required: true,
  },
  entityRandomId: {
    type: String,
    required: true,
  },
  identifier: {
    type: String,
    required: true,
  },
  isShowDeleted: {
    type: Boolean,
    required: false,
  },
  filterName: {
    type: Object,
    required: false,
  },
})

const { t } = useI18n();

const auditList = ref([])
const loading = ref(true);
const tableParams = ref({});
const tableHeaders = ref([])
const advanceFilters = ref([])
const searchColumn = ref([])
const pageLoading = ref(true);
const selectedColumns = ref([])
const url = ref();
const compareURL = ref();
const exportURL = ref();
const exportPdfURL = ref();
const exportColumns = ref([]);
const exportPermission = ref(false);
const entityId = props.entityRandomId;
const activitiesCompareDialog = ref(false);
const activitiesCompareDialogRef = ref();
const { getEntityAuditTrailListingConfig, saveAuditTrailTableFilters, getEntityAuditTrailListing } = useAuditTrailApiClient();

const columnFilterSave = async (selectedColumn: Array<string>) => {
  await saveAuditTrailTableFilters(selectedColumn, props.identifier, props.filterName)
}

getEntityAuditTrailListingConfig({ module: props.module, submodule: props.submodule, identifier: props.identifier, filterName: props.filterName })
  .then(response => {
    tableHeaders.value = response.tableColumns
    advanceFilters.value = response.advanceFilters;
    searchColumn.value = response.columnSearchable;
    selectedColumns.value = response.selectedColumns;
    pageLoading.value = false;
    url.value = response.url;
    compareURL.value = response.compareURL;
    exportURL.value = response.exportURL;
    exportPdfURL.value = response.exportPdfURL;
    exportColumns.value = response.exportColumns;
    exportPermission.value = response.exportPermission;
  })

const reloadListingData = async (params: ExportListingFormData) => {
  params = {
    ...params,
    entityId,
  }
  loading.value = true
  auditList.value = await getEntityAuditTrailListing(url.value, params)
  loading.value = false
}

const showCompareDialog = (id: number) => {
  const activityId = id;

  const params = {
    activityId,
  }

  activitiesCompareDialogRef.value.fetchAuditTrailCompare(params);
  activitiesCompareDialog.value = true;
}
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
        <VCardItem class="notification-section">
          <DataTable
            v-model="tableParams"
            :headers="tableHeaders"
            :listData="auditList.data"
            :totalItems="auditList.dataCount"
            :loading="loading"
            :showExportButton="!!exportURL && exportPermission"
            :showPrintButton="!!exportPdfURL && exportPermission"
            :advanceFilters="advanceFilters"
            :selectedColumns="selectedColumns"
            :tableFilterIdentifier="identifier"
            :columnSearchable="searchColumn"
            :exportColumns="exportColumns"
            @loadItems="reloadListingData"
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
        <!-- 👉 Header -->
      </VCard>
    </template>
  </div>
  <ActivitesCompare
    ref="activitiesCompareDialogRef"
    v-model:isDialogOpen="activitiesCompareDialog"
    :compareURL="compareURL"
    :activityId="activityId"
    :entityId="false"
  />
</template>
