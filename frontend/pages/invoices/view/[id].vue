<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'invoices',
  checkPermission: 'invoices.view',
})

useHead({
  title: t('viewInvoice'),
})

const route = useRoute()
const uuid = ref(route.params.id as string)
const invoiceDetailData = ref<any>(null)
const permission = ref<{ writePermission?: boolean; restorePermission?: boolean }>({})
const tabItems = ref<AppTab[]>([])
const loading = ref(true)
const pdfViewerDialog = ref(false)
const pdfUrl = ref('')

const { getInvoiceViewById, getInvoicePrintUrl } = useInvoiceApiClient()

const paymentStatusColor = (status: string) => {
  if (status === 'paid') return 'success'
  if (status === 'partial') return 'warning'
  return 'error'
}

const openPrintDialog = () => {
  pdfUrl.value = getInvoicePrintUrl(uuid.value)
  pdfViewerDialog.value = true
}

const printDoc = () => {
  window.frames.iFramePrintViewer.focus()
  window.frames.iFramePrintViewer.print()
}

const reloadData = async () => {
  loading.value = true

  const response = await getInvoiceViewById(uuid.value)

  invoiceDetailData.value = (response as any)?.invoice ?? response
  permission.value = (response as any)?.permissions ?? {}

  tabItems.value = [
    {
      icon: 'tabler-receipt',
      tab: 'invoiceInfo',
      title: t('info'),
      pinnedTab: true,
      component: markRaw(resolveComponent('InvoiceViewInfo') as object),
    },
  ]

  loading.value = false
}

reloadData()
</script>

<template>
  <template v-if="loading">
    <div class="tw-mb-4">
      <VSkeletonLoader
        :loading="loading"
        type="heading@1"
      />
    </div>
    <div class="tw-grid tw-grid-cols-5 tw-gap-2 tw-justify-between">
      <div>
        <VSkeletonLoader
          :loading="loading"
          type="card"
        />
        <VSkeletonLoader
          :loading="loading"
          type="heading@4"
          class="tw-mt-2"
        />
      </div>
      <div class="tw-col-span-4">
        <VSkeletonLoader
          :loading="loading"
          type="heading@1"
          class="tw-mb-4"
        />
        <VSkeletonLoader
          :loading="loading"
          type="heading@4"
        />
      </div>
    </div>
  </template>

  <template v-else>
    <div>
      <VCard class="tw-grid tw-grid-cols-2 tw-justify-between tw-items-center tw-mb-6 sticky-header tw-p-4">
        <div>
          <div class="tw-flex tw-gap-4 tw-items-center">
            <div class="tw-text-2xl tw-font-bold">
              {{ invoiceDetailData?.invoice_number ?? invoiceDetailData?.invoiceNumber }}
            </div>
          </div>
          <div class="tw-mt-2 tw-ms-2 tw-inline-flex">
            <VChip
              class="tw-mr-1"
              size="small"
              :color="paymentStatusColor(invoiceDetailData?.payment_status ?? invoiceDetailData?.paymentStatus)"
              variant="tonal"
            >
              {{ t(invoiceDetailData?.payment_status ?? invoiceDetailData?.paymentStatus ?? 'unpaid') }}
            </VChip>
          </div>
        </div>

        <div class="tw-flex tw-flex-col tw-gap-4 tw-justify-between">
          <div class="tw-flex tw-gap-2 tw-justify-end">
            <div v-if="invoiceDetailData?.deleted_at">
              <DeletedAlert :data="invoiceDetailData" />
            </div>
            <div class="tw-flex tw-gap-2">
              <!-- Print Button -->
              <VTooltip :text="t('print')">
                <template #activator="{ props }">
                  <div v-bind="props">
                    <VBtn
                      size="38"
                      color="secondary"
                      :disabled="!!invoiceDetailData?.deleted_at"
                      @click="openPrintDialog"
                    >
                      <VIcon
                        icon="tabler-printer"
                        size="22"
                      />
                    </VBtn>
                  </div>
                </template>
              </VTooltip>

              <VTooltip
                v-if="permission?.writePermission"
                :text="t('edit')"
              >
                <template #activator="{ props }">
                  <div v-bind="props">
                    <VBtn
                      size="38"
                      :disabled="!!invoiceDetailData?.deleted_at"
                      :color="invoiceDetailData?.deleted_at ? 'secondary' : 'primary'"
                      @click="navigateTo(`/invoices/form/${invoiceDetailData?.uuid}`)"
                    >
                      <VIcon
                        icon="tabler-edit"
                        size="22"
                      />
                    </VBtn>
                  </div>
                </template>
              </VTooltip>

              <VTooltip
                v-if="permission?.restorePermission && invoiceDetailData?.deleted_at"
                :text="t('restore')"
              >
                <template #activator="{ props }">
                  <div v-bind="props">
                    <VBtn size="38">
                      <VIcon
                        icon="tabler-restore"
                        size="22"
                      />
                    </VBtn>
                  </div>
                </template>
              </VTooltip>
            </div>
          </div>

          <div class="tw-gap-2 tw-justify-end">
            <div class="tw-flex tw-justify-end tw-gap-1 tw-ml-2">
              <div class="tw-font-normal tw-flex-shrink-0">
                {{ t('createdBy') }} :
              </div>
              <div>
                <span class="tw-font-normal">{{ displayOrFallback(invoiceDetailData?.creator?.full_name || '') }}</span>
              </div>
            </div>
          </div>
        </div>
      </VCard>

      <AppTabs
        :tabItems="tabItems"
        :randomId="uuid"
        :entityData="invoiceDetailData"
      >
        <template #middleColumnData>
          <VCard width="100%">
            <VCardText class="pl-2 pt-2">
              <span class="d-flex mt-2 tw-items-center">
                <VTooltip :text="t('dateCreated')">
                  <template #activator="{ props }">
                    <VIcon
                      v-bind="props"
                      size="25"
                      icon="tabler-calendar-plus"
                      class="me-1"
                    />
                  </template>
                </VTooltip>
                <span class="text-body-1">
                  <VLabel class="tw-font-semibold">{{ displayOrFallback(invoiceDetailData?.created_at) }}</VLabel>
                </span>
              </span>
              <span class="d-flex mt-2 tw-items-center">
                <VTooltip :text="t('dateModified')">
                  <template #activator="{ props }">
                    <VIcon
                      v-bind="props"
                      size="25"
                      icon="mdi-calendar-edit-outline"
                      class="me-1"
                    />
                  </template>
                </VTooltip>
                <span class="text-body-1">
                  <VLabel class="tw-font-semibold">{{ displayOrFallback(invoiceDetailData?.updated_at) }}</VLabel>
                </span>
              </span>
            </VCardText>
          </VCard>
        </template>
      </AppTabs>
    </div>
  </template>

  <!-- PDF Print Dialog -->
  <VDialog
    v-model="pdfViewerDialog"
    width="60%"
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
          style="block-size: 80vh;"
          :src="pdfUrl"
          frameborder="0"
          @load="printDoc"
        />
      </VCardText>
    </VCard>
  </VDialog>
</template>
