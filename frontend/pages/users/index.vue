<script setup lang="ts">
/**
 * Staff listing — config skeleton, then DataTable row loading:
 * - configLoading → VSkeletonLoader type="table"
 * - listLoading → DataTable :loading (row fetch overlay)
 * - One composable instance (config + list loading flags separated inside client)
 */

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

const {
  userDataTableConfig,
  userDataTableResult,
  configLoading,
  listLoading,
  getUserDataTableConfig,
  getUserDataTableData,
  saveUserTableFilters,
  deleteUsers,
  disableUsers,
  enableUsers,
  exportUsersListing,
} = useUserApiClient()

const auth = useAuthStore()

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
      const uuid = auth.user?.uuid
      if (uuid)
        showBulkActionProgress(`bulkAction.users.${uuid}`)

      await enableUsers({
        uuids: selectedItemIds.value,
      })
      await reloadListingData()
    }

    return
  }

  reasonFormDialogOpen.value = true
  actionToBeSubmit = action
}

const handleReasonFormSubmit = async (reasonForm: any) => {
  const uuid = auth.user?.uuid
  if (uuid)
    showBulkActionProgress(`bulkAction.users.${uuid}`)

  const params = {
    uuids: selectedItemIds.value,
    reason: reasonForm.reason,
  }

  let response

  if (actionToBeSubmit === 'delete')
    response = await deleteUsers(params)
  else
    response = await disableUsers(params)

  if (response) {
    resetBulkAction.value?.resetBulkOptions?.()
    await reloadListingData()
    reasonFormDialogOpen.value = false
    reasonFormDialogSubmitDisable.value = false
  }
}

const columnFilterSave = async (filterData: { [key: string]: any }) => {
  await saveUserTableFilters(filterData)
}

const closeReasonDialog = (value: boolean) => {
  reasonFormDialogOpen.value = value
}

const loadConfig = async () => {
  await getUserDataTableConfig()
}

loadConfig()
</script>

<template>
  <div>
    <template v-if="configLoading">
      <VSkeletonLoader
        :loading="configLoading"
        type="table"
      />
    </template>
    <template v-else>
      <DataTable
        v-bind="userDataTableConfig"
        ref="resetBulkAction"
        v-model:selectedItemIds="selectedItemIds"
        v-model="tableParams"
        :list-data="userDataTableResult.data"
        :total-items="userDataTableResult.pagination?.total ?? 0"
        :loading="listLoading"
        checkbox-id-key="uuid"
        show-add-button
        :show-export-button="false"
        :table-filter-identifier="USER_LISTING_FILTER_IDENTIFIER"
        @load-items="reloadListingData"
        @export-listing="handleExportDocumentsListing"
        @column-filter-change="columnFilterSave"
        @bulk-action-trigger="bulkAction"
        @add-new="navigateTo('/users/form')"
      >
        <template #actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn
              v-if="item.actions?.writePermission"
              size="38"
              color="primary"
              :disabled="item.actions?.isLocked"
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
              v-if="item.actions?.viewPermission"
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
    :dialog-open="reasonFormDialogOpen"
    :alert-lang="$t('whyYouWantToDoThisAction')"
    :signing-dialog-button-disabled="reasonFormDialogSubmitDisable"
    @updated-dialog-value="closeReasonDialog"
    @sign-form-data="handleReasonFormSubmit"
  />
</template>
