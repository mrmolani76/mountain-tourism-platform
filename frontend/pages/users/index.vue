<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'users',
  checkPermission: 'users.view',
})

useHead({
  title: t('users'),
})

let actionToBeSubmit = ''

const resetBulkAction = ref()
const selectedItemIds = ref([])
const reasonFormDialogOpen = ref(false)
const reasonFormDialogSubmitDisable = ref(false)
const tableParams = ref({})
const { userDataTableConfig, loading: isLoading, getUserDataTableConfig, saveUserTableFilters } = useUserApiClient()
const { userDataTableResult, loading: listLoading, getUserDataTableData, deleteUsers, disableUsers, enableUsers, exportUsersListing } = useUserApiClient()

const reloadListingData = async (params: any = tableParams.value) => {
  selectedItemIds.value = []

  await getUserDataTableData(params)
}

const handleExportDocumentsListing = async (exportParams: any) => {
  const response = await exportUsersListing(exportParams)

  if (!response?.status)
    return

  showAlertDialog(t('notification'), response.message, t('ok'))
}

const bulkAction = async (action: string) => {
  if (action === 'enable') {
    if (await showConfirmDialog()) {
      showBulkActionProgress(`bulkAction.users.${authStore().user.uuid}`)
      await enableUsers({
        uuids: selectedItemIds.value,
      });
      reloadListingData()
    }

    return;
  }
  reasonFormDialogOpen.value = true;
  actionToBeSubmit = action;
}

const handleReasonFormSubmit = async (reasonForm: any) => {
  showBulkActionProgress(`bulkAction.users.${authStore().user.uuid}`)

  const params = {
    uuids: selectedItemIds.value,
    reason: reasonForm.reason,
  }

  let response;

  if (actionToBeSubmit === 'delete')
    response = await deleteUsers(params);
  else
    response = await disableUsers(params);

  if (response) {
    resetBulkAction.value.resetBulkOptions()
    reloadListingData()
    reasonFormDialogOpen.value = false;
    reasonFormDialogSubmitDisable.value = false;
  }
}

const columnFilterSave = async (filterData: { [key: string]: any }) => {
  await saveUserTableFilters(filterData)
}

const closeReasonDialog = (value: boolean) => {
  reasonFormDialogOpen.value = value
}

(async () => {
  await getUserDataTableConfig()
})();
</script>

<template>
  <div>
    <template v-if="isLoading">
      <VSkeletonLoader
        :loading="isLoading"
        type="table"
      />
    </template>
    <template v-else>
      <DataTable
        v-bind="userDataTableConfig"
        ref="resetBulkAction"
        v-model:selectedItemIds="selectedItemIds"
        v-model="tableParams"
        :listData="userDataTableResult.data"
        :totalItems="userDataTableResult.pagination.total"
        :loading="listLoading"
        checkboxIdKey="uuid"
        showAddButton
        :showExportButton="false"
        :tableFilterIdentifier="USER_LISTING_FILTER_IDENTIFIER"
        @loadItems="reloadListingData"
        @exportListing="handleExportDocumentsListing"
        @columnFilterChange="columnFilterSave"
        @bulkActionTrigger="bulkAction"
        @addNew="navigateTo('/users/form')"
      >
        <template #actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn
              v-if="item.actions.writePermission"
              size="38"
              color="primary"
              :disabled="item.actions.isLocked"
              @click="navigateTo(`/users/form/${item.uuid}`)"
            >
              <VTooltip activator="parent">
                {{ t('edit') }}
              </VTooltip>
              <VIcon
                size="22"
                icon="tabler-edit"
              />
            </VBtn>
            <VBtn
              v-if="item.actions.viewPermission"
              size="38"
              color="primary"
              @click="navigateTo(`/users/view/${item.uuid}`)"
            >
              <VTooltip activator="parent">
                {{ t('view') }}
              </VTooltip>
              <VIcon
                icon="tabler-eye"
                size="22"
              />
            </VBtn>
          </div>
        </template>
      </DataTable>
    </template>
  </div>
  <ReasonDialogForm
    :dialogOpen="reasonFormDialogOpen"
    :alertLang="$t('whyYouWantToDoThisAction')"
    :signingDialogButtonDisabled="reasonFormDialogSubmitDisable"
    @updatedDialogValue="closeReasonDialog"
    @signFormData="handleReasonFormSubmit"
  />
</template>
