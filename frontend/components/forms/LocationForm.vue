<script setup lang="ts">
const props = withDefaults(defineProps<{
  addressForm: AddressForm
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

const form = ref({} as AddressForm)
const showLocationSelectorDialog = ref(false)
const selectedAddress = ref()

watch(() => props.addressForm, value => {
  form.value = {
    ...value,
    latLng: value.lat ? `${value.lat},${value.lng}` : '',
  }
}, { immediate: true })

const addressParts = [
  form.value.city,
  form.value.address1,
  form.value.address2,
  form.value.zip,
].filter(part => part && part.trim() !== '')

const openLocationSelector = () => {
  showLocationSelectorDialog.value = true;

  selectedAddress.value = addressParts.length > 0 ? addressParts.join(', ') : ''
}

const setLocation = async (location: Address) => {
  if (location.lat && location.lng) {
    form.value.latLng = `${location.lat},${location.lng}`;
    form.value.lat = location.lat;
    form.value.lng = location.lng;
  }

  // only files if already not filled up
  if (location.address1 && !form.value.address1)
    form.value.address1 = location.address1

  if (location.address2 && !form.value.address2)
    form.value.address2 = location.address2

  if (location.city && !form.value.city)
    form.value.city = location.city

  if (location.zip && !form.value.zip)
    form.value.zip = location.zip

  showLocationSelectorDialog.value = false;
}

defineExpose({
  form,
})
</script>

<template>
  <VCard
    flat
    width="100%"
  >
    <div class="tw-mb-2 tw-text-lg tw-mt-2 tw-ml-4">
      {{ $t('location') }}
    </div>
    <VDivider
      :thickness="1"
      class="border-opacity-100"
      color="secondary"
    />
    <VCardText>
      <VRow>
        <VCol cols="12">
          <VLabel>
            {{ $t('location') }}
            <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
          </VLabel>

          <AppTextField
            v-model="form.latLng"
            clearable
            :placeholder="$t('selectLocation')"
            :rules="[requiredValidator, locationValidator]"
            class="textfield-demo-icon-slot"
          >
            <!-- Append -->
            <template #append>
              <VBtn @click="openLocationSelector">
                <VIcon
                  icon="tabler-map-pin"
                  color="#fff"
                  size="22"
                />
              </VBtn>
            </template>
          </AppTextField>
        </VCol>
      </VRow>
      <VRow>
        <VCol cols="12">
          <VLabel>
            {{ $t('addressLine1') }}
            <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
          </VLabel>
          <AppTextField
            v-model="form.address1"
            placeholder=""
            :rules="[requiredValidator, textMaxValidator(form.address1, TEXT_MAX_LIMIT)]"
            required="true"
          />
        </VCol>
      </VRow>
      <VRow>
        <VCol cols="12">
          <VLabel>
            {{ $t('addressLine2') }}
            <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
          </VLabel>
          <AppTextField
            v-model="form.address2"
            placeholder=""
            :rules="[requiredValidator, textMaxValidator(form.address2, TEXT_MAX_LIMIT)]"
            required="true"
          />
        </VCol>
      </VRow>
      <VRow>
        <VCol cols="12">
          <VLabel>
            {{ $t('city') }}
            <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
          </VLabel>
          <AppTextField
            v-model="form.city"
            placeholder=""
            :rules="[requiredValidator]"
            required="true"
          />
        </VCol>
      </VRow>
      <VRow>
        <VCol cols="12">
          <VLabel>
            {{ $t('zip') }}
            <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
          </VLabel>
          <AppTextField
            v-model="form.zip"
            placeholder=""
            :rules="[requiredValidator, zipCodeValidator]"
            required="true"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
  <LocationSelectorDialog
    v-model:isDialogVisible="showLocationSelectorDialog"
    :location="form.latLng"
    :selectedAddress="selectedAddress"
    @onSubmit="setLocation"
  />
</template>
