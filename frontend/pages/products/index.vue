<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
    navActiveLink: 'products',
    checkPermission: 'products.view',
})

useHead({
    title: t('products'),
})

let actionToBeSubmit = ''

const resetBulkAction = ref()
const selectedItemIds = ref([])
const reasonFormDialogOpen = ref(false)
const reasonFormDialogSubmitDisable = ref(false)
const tableParams = ref({})
const { productDataTableConfig, loading: isLoading, getProductDataTableConfig, saveProductTableFilters } = useProductApiClient()
const { productDataTableResult, loading: listLoading, getProductDataTableData, deleteProducts, disableProducts, enableProducts, exportProductsListing } = useProductApiClient()

const reloadListingData = async (params: any = tableParams.value) => {
    selectedItemIds.value = []

    await getProductDataTableData(params)
}

const handleExportProductsListing = async (exportParams: any) => {
    const response = await exportProductsListing(exportParams)

    if (!response?.status)
        return

    showAlertDialog(t('notification'), response.message, t('ok'))
}

const bulkAction = async (action: string) => {
    if (action === 'enable') {
        if (await showConfirmDialog()) {
            showBulkActionProgress(`bulkAction.products.${authStore().user.uuid}`)
            await enableProducts({
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
    showBulkActionProgress(`bulkAction.products.${authStore().user.uuid}`)

    const params = {
        uuids: selectedItemIds.value,
        reason: reasonForm.reason,
    }

    let response;

    if (actionToBeSubmit === 'delete')
        response = await deleteProducts(params);
    else
        response = await disableProducts(params);

    if (response) {
        resetBulkAction.value.resetBulkOptions()
        reloadListingData()
        reasonFormDialogOpen.value = false;
        reasonFormDialogSubmitDisable.value = false;
    }
}

const columnFilterSave = async (filterData: { [key: string]: any }) => {
    await saveProductTableFilters(filterData)
}

const closeReasonDialog = (value: boolean) => {
    reasonFormDialogOpen.value = value
}

(async () => {
    await getProductDataTableConfig()
})();
</script>

<template>
    <div>
        <template v-if="isLoading">
            <VSkeletonLoader :loading="isLoading" type="table" />
        </template>
        <template v-else>
            <DataTable v-bind="productDataTableConfig" ref="resetBulkAction" v-model:selectedItemIds="selectedItemIds"
                v-model="tableParams" :listData="productDataTableResult.data"
                :totalItems="productDataTableResult.pagination.total" :loading="listLoading" checkboxIdKey="uuid"
                showAddButton :showExportButton="true" :tableFilterIdentifier="PRODUCT_LISTING_FILTER_IDENTIFIER"
                @loadItems="reloadListingData" @exportListing="handleExportProductsListing"
                @columnFilterChange="columnFilterSave" @bulkActionTrigger="bulkAction"
                @addNew="navigateTo('/products/form')">
                <template #actions="{ item }">
                    <div class="d-flex gap-1">
                        <VBtn v-if="item.actions.writePermission" size="38" color="primary"
                            :disabled="item.actions.isLocked" @click="navigateTo(`/products/form/${item.uuid}`)">
                            <VTooltip activator="parent">
                                {{ t('edit') }}
                            </VTooltip>
                            <VIcon size="22" icon="tabler-edit" />
                        </VBtn>
                        <VBtn v-if="item.actions.viewPermission" size="38" color="primary"
                            @click="navigateTo(`/products/view/${item.uuid}`)">
                            <VTooltip activator="parent">
                                {{ t('view') }}
                            </VTooltip>
                            <VIcon icon="tabler-eye" size="22" />
                        </VBtn>
                    </div>
                </template>

                <!-- Custom column templates for better display -->
                <template #item-selling_price="{ item }">
                    <span>₹{{ Number(item.selling_price).toLocaleString('en-IN') }}</span>
                </template>

                <template #item-purchase_price="{ item }">
                    <span>₹{{ Number(item.purchase_price).toLocaleString('en-IN') }}</span>
                </template>

                <template #item-quantity="{ item }">
                    <VChip :color="(item.quantity || 0) > (item.alert_quantity || 0) ? 'success' : 'error'"
                        size="small">
                        {{ item.quantity || 0 }} {{ item.unit || 'pcs' }}
                    </VChip>
                </template>

                <template #item-status="{ item }">
                    <VChip
                        :color="item.status === 'active' ? 'success' : item.status === 'inactive' ? 'warning' : 'error'"
                        size="small">
                        {{ t(item.status) }}
                    </VChip>
                </template>
            </DataTable>
        </template>
    </div>
    <ReasonDialogForm :dialogOpen="reasonFormDialogOpen" :alertLang="$t('whyYouWantToDoThisAction')"
        :signingDialogButtonDisabled="reasonFormDialogSubmitDisable" @updatedDialogValue="closeReasonDialog"
        @signFormData="handleReasonFormSubmit" />
</template>
