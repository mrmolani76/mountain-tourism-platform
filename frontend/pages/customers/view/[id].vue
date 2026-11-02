<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'customers',
  checkPermission: 'customers.view',
})

useHead({
  title: t('viewCustomer'),
})

const route = useRoute()
const uuid = ref(route.params.id as string)
const customerDetailData = ref<any>(null)
const permission = ref<{ writePermission?: boolean; restorePermission?: boolean }>({})
const tabItems = ref<AppTab[]>([])
const loading = ref(true)
const { getCustomerViewById } = useCustomerApiClient()

const reloadData = async () => {
  loading.value = true

  const response = await getCustomerViewById(uuid.value)

  customerDetailData.value = (response as any)?.customer ?? response
  permission.value = (response as any)?.permissions ?? {}

  tabItems.value = [
    {
      icon: 'tabler-info-circle',
      tab: 'customerInfo',
      title: t('info'),
      pinnedTab: true,
      component: markRaw(resolveComponent('CustomerViewInfo') as object),
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
              {{ customerDetailData?.name }}
            </div>
          </div>
          <div class="tw-mt-2 tw-ms-2 tw-inline-flex">
            <VChip
              class="tw-mr-1"
              size="small"
              :color="customerDetailData?.status === 'active' ? 'success' : 'error'"
              variant="tonal"
            >
              {{ t(customerDetailData?.status ?? 'inactive') }}
            </VChip>
          </div>
        </div>

        <div class="tw-flex tw-flex-col tw-gap-4 tw-justify-between">
          <div class="tw-flex tw-gap-2 tw-justify-end">
            <div v-if="customerDetailData?.deleted_at">
              <DeletedAlert :data="customerDetailData" />
            </div>
            <div class="tw-flex tw-gap-2">
              <VTooltip
                v-if="permission?.writePermission"
                :text="t('edit')"
              >
                <template #activator="{ props }">
                  <div v-bind="props">
                    <VBtn
                      size="38"
                      :disabled="!!customerDetailData?.deleted_at"
                      :color="customerDetailData?.deleted_at ? 'secondary' : 'primary'"
                      @click="navigateTo(`/customers/form/${customerDetailData?.uuid}`)"
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
                v-if="permission?.restorePermission && customerDetailData?.deleted_at"
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
                <span class="tw-font-normal">{{ displayOrFallback(customerDetailData?.creator?.full_name || '') }}</span>
              </div>
            </div>
          </div>
        </div>
      </VCard>

      <AppTabs
        :tabItems="tabItems"
        :randomId="uuid"
        :entityData="customerDetailData"
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
                  <VLabel class="tw-font-semibold">{{ displayOrFallback(customerDetailData?.created_at) }}</VLabel>
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
                  <VLabel class="tw-font-semibold">{{ displayOrFallback(customerDetailData?.updated_at) }}</VLabel>
                </span>
              </span>
            </VCardText>
          </VCard>
        </template>
      </AppTabs>
    </div>
  </template>
</template>
