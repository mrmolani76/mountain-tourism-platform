<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'invoices',
  checkPermission: 'invoices.view',
})

useHead({
  title: t('invoices'),
})

let actionToBeSubmit = ''

const resetBulkAction = ref()
const selectedItemIds = ref([])
const reasonFormDialogOpen = ref(false)
const reasonFormDialogSubmitDisable = ref(false)
const tableParams = ref({})
const { invoiceDataTableConfig, loading: isLoading, getInvoiceDataTableConfig, saveInvoiceTableFilters } = useInvoiceApiClient()
const { invoiceDataTableResult, loading: listLoading, getInvoiceDataTableData, deleteInvoice } = useInvoiceApiClient()

const reloadListingData = async (params: any = tableParams.value) => {
  selectedItemIds.value = []
  await getInvoiceDataTableData(params)
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
    response = await deleteInvoice(params)

  if (response) {
    resetBulkAction.value.resetBulkOptions()
    reloadListingData()
    reasonFormDialogOpen.value = false
    reasonFormDialogSubmitDisable.value = false
  }
}

const columnFilterSave = async (filterData: { [key: string]: any }) => {
  await saveInvoiceTableFilters(filterData)
}

const closeReasonDialog = (value: boolean) => {
  reasonFormDialogOpen.value = value
}

const paymentStatusColor = (status: string) => {
  if (status === 'paid') return 'success'
  if (status === 'partial') return 'warning'
  return 'error'
}

(async () => {
  await getInvoiceDataTableConfig()
})()
</script>

<template>
  <div>
    <template v-if="isLoading">
      <VSkeletonLoader :loading="isLoading" type="table" />
    </template>
    <template v-else>
      <DataTable
        v-bind="invoiceDataTableConfig"
        ref="resetBulkAction"
        v-model:selectedItemIds="selectedItemIds"
        v-model="tableParams"
        :listData="invoiceDataTableResult.data"
        :totalItems="invoiceDataTableResult.pagination.total"
        :loading="listLoading"
        checkboxIdKey="uuid"
        showAddButton
        :tableFilterIdentifier="INVOICE_LISTING_FILTER_IDENTIFIER"
        @loadItems="reloadListingData"
        @columnFilterChange="columnFilterSave"
        @bulkActionTrigger="bulkAction"
        @addNew="navigateTo('/invoices/form')"
      >
        <template #actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn
              v-if="item.actions.writePermission"
              size="38"
              color="primary"
              :disabled="item.actions.isLocked"
              @click="navigateTo(`/invoices/form/${item.uuid}`)"
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
              @click="navigateTo(`/invoices/view/${item.uuid}`)"
            >
              <VTooltip activator="parent">
                {{ t('view') }}
              </VTooltip>
              <VIcon icon="tabler-eye" size="22" />
            </VBtn>
          </div>
        </template>

        <template #item-invoice_number="{ item }">
          <span
            class="tw-text-primary tw-cursor-pointer tw-font-medium"
            @click="navigateTo(`/invoices/view/${item.uuid}`)"
          >
            {{ item.invoice_number ?? item.invoiceNumber }}
          </span>
        </template>

        <template #item-total_amount="{ item }">
          <span>₹{{ Number(item.total_amount ?? item.totalAmount ?? 0).toLocaleString('en-IN') }}</span>
        </template>

        <template #item-payment_status="{ item }">
          <VChip
            :color="paymentStatusColor(item.payment_status ?? item.paymentStatus)"
            size="small"
          >
            {{ t(item.payment_status ?? item.paymentStatus) }}
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
