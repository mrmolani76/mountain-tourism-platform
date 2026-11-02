<script setup lang="ts">
withDefaults(defineProps<{
  address: Address
}>(), {
  addressForm: () => ({
    address1: '',
    address2: '',
    city: '',
    zip: '',
    lat: 0,
    lng: 0,
    latLng: '',
  }),

})

const showLocationSelectorDialog = ref(false)

const openLocationSelector = () => {
  showLocationSelectorDialog.value = true;
}
</script>

<template>
  <VCard>
    <div class="tw-mb-4 tw-text-lg tw-mt-4 tw-ml-4">
      {{ $t('location') }}
    </div>
    <VDivider
      :thickness="1"
      class="border-opacity-100"
      color="secondary"
    />
    <VList width="100%">
      <VListItem>
        <div class="tw-flex tw-gap-2">
          <div class="tw-font-semibold tw-flex-shrink-0">
            {{ $t('location') }}
          </div>
          :
          <div>
            <VBtn
              icon="tabler-map-2"
              color="primary"
              size="32"
              @click="openLocationSelector"
            />
          </div>
        </div>
      </VListItem>
      <VListItem>
        <LabelValueDisplay
          label="addressLine1"
          :value="address?.address1"
        />
      </VListItem>
      <VListItem>
        <LabelValueDisplay
          label="addressLine2"
          :value="address?.address2"
        />
      </VListItem>
      <VListItem>
        <LabelValueDisplay
          label="city"
          :value="address?.city"
        />
      </VListItem>
      <VListItem>
        <LabelValueDisplay
          label="zip"
          :value="address?.zip"
        />
      </VListItem>
    </VList>
  </VCard>
  <LocationSelectorDialog
    v-model:isDialogVisible="showLocationSelectorDialog"
    :location="`${address?.lat},${address?.lng}`"
    :selectedAddress="`${address?.city} ${address?.address1} ${address?.address2} ${address?.zip}`"
    isReadonly
  />
</template>
