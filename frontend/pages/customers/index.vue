<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'customers',
  checkPermission: 'customers.view',
})

useHead({
  title: t('customers'),
})

let actionToBeSubmit = ''

const resetBulkAction = ref()
const selectedItemIds = ref([])
const reasonFormDialogOpen = ref(false)
const reasonFormDialogSubmitDisable = ref(false)
const tableParams = ref({})
const { customerDataTableConfig, loading: isLoading, getCustomerDataTableConfig, saveCustomerTableFilters } = useCustomerApiClient()
const { customerDataTableResult, loading: listLoading, getCustomerDataTableData, deleteCustomer } = useCustomerApiClient()

const reloadListingData = async (params: any = tableParams.value) => {
  selectedItemIds.value = []
  await getCustomerDataTableData(params)
}

const bulkAction = async (action: string) => {
  reasonFormDialogOpen.value = true
  actionToBeSubmit = action
}

const handleReasonFormSubmit = async (reasonForm: any) => {
  const params = {
    uuids: selectedItemIds.value,
    reason: reasonForm.reason,
  }

  let response

  if (actionToBeSubmit === 'delete')
    response = await deleteCustomer(params)

  if (response) {
    resetBulkAction.value.resetBulkOptions()
    reloadListingData()
    reasonFormDialogOpen.value = false
    reasonFormDialogSubmitDisable.value = false
  }
}

const columnFilterSave = async (filterData: { [key: string]: any }) => {
  await saveCustomerTableFilters(filterData)
}

const closeReasonDialog = (value: boolean) => {
  reasonFormDialogOpen.value = value
}

(async () => {
  await getCustomerDataTableConfig()
})()
</script>

<template>
  <div>
    <template v-if="isLoading">
      <VSkeletonLoader :loading="isLoading" type="table" />
    </template>
    <template v-else>
      <DataTable
        v-bind="customerDataTableConfig"
        ref="resetBulkAction"
        v-model:selectedItemIds="selectedItemIds"
        v-model="tableParams"
        :listData="customerDataTableResult.data"
        :totalItems="customerDataTableResult.pagination.total"
        :loading="listLoading"
        checkboxIdKey="uuid"
        showAddButton
        :tableFilterIdentifier="CUSTOMER_LISTING_FILTER_IDENTIFIER"
        @loadItems="reloadListingData"
        @columnFilterChange="columnFilterSave"
        @bulkActionTrigger="bulkAction"
        @addNew="navigateTo('/customers/form')"
      >
        <template #actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn
              v-if="item.actions.writePermission"
              size="38"
              color="primary"
              :disabled="item.actions.isLocked"
              @click="navigateTo(`/customers/form/${item.uuid}`)"
            >
              <VTooltip activator="parent">
                {{ t('edit') }}
              </VTooltip>
              <VIcon size="22" icon="tabler-edit" />
            </VBtn>
            <VBtn
              v-if="item.actions.viewPermission"
              size="38"
              color="primary"
              @click="navigateTo(`/customers/view/${item.uuid}`)"
            >
              <VTooltip activator="parent">
                {{ t('view') }}
              </VTooltip>
              <VIcon icon="tabler-eye" size="22" />
            </VBtn>
          </div>
        </template>

        <template #item-status="{ item }">
          <VChip
            :color="item.status === 'active' ? 'success' : item.status === 'inactive' ? 'warning' : 'error'"
            size="small"
          >
            {{ t(item.status) }}
          </VChip>
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
