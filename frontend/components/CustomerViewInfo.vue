<script setup lang="ts">
const props = defineProps({
  entityData: {
    type: Object,
    default: () => ({}),
  },
})

const { t } = useI18n()
</script>

<template>
  <VCard>
    <VCardText>
      <div class="tw-grid tw-grid-cols-2 tw-gap-x-8 tw-gap-y-4">
        <div>
          <VLabel class="tw-text-xs tw-text-gray-500">{{ t('name') }}</VLabel>
          <div class="tw-font-medium tw-mt-1">
            {{ displayOrFallback(entityData?.name) }}
          </div>
        </div>

        <div>
          <VLabel class="tw-text-xs tw-text-gray-500">{{ t('email') }}</VLabel>
          <div class="tw-font-medium tw-mt-1">
            {{ displayOrFallback(entityData?.email) }}
          </div>
        </div>

        <div>
          <VLabel class="tw-text-xs tw-text-gray-500">{{ t('phone') }}</VLabel>
          <div class="tw-font-medium tw-mt-1">
            {{ displayOrFallback(entityData?.phone) }}
          </div>
        </div>

        <div>
          <VLabel class="tw-text-xs tw-text-gray-500">{{ t('gstNumber') }}</VLabel>
          <div class="tw-font-medium tw-mt-1">
            {{ displayOrFallback(entityData?.gstNumber ?? entityData?.gst_number) }}
          </div>
        </div>

        <div class="tw-col-span-2">
          <VLabel class="tw-text-xs tw-text-gray-500">{{ t('address') }}</VLabel>
          <div class="tw-font-medium tw-mt-1">
            {{ displayOrFallback(entityData?.address) }}
          </div>
        </div>

        <div>
          <VLabel class="tw-text-xs tw-text-gray-500">{{ t('city') }}</VLabel>
          <div class="tw-font-medium tw-mt-1">
            {{ displayOrFallback(entityData?.city) }}
          </div>
        </div>

        <div>
          <VLabel class="tw-text-xs tw-text-gray-500">{{ t('state') }}</VLabel>
          <div class="tw-font-medium tw-mt-1">
            {{ displayOrFallback(entityData?.state) }}
          </div>
        </div>

        <div>
          <VLabel class="tw-text-xs tw-text-gray-500">{{ t('zip') }}</VLabel>
          <div class="tw-font-medium tw-mt-1">
            {{ displayOrFallback(entityData?.zip) }}
          </div>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>
