<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'customers',
  checkPermission: 'customers.add',
})

const route = useRoute()
const customerId = computed(() => route.params.id as string)
const isEditMode = computed(() => !!customerId.value)
const nuxtApp = useNuxtApp()
const isButtonDisabled = ref(false)
const submitLabel = computed(() => isEditMode.value ? t('update') : t('save'))

useHead({
  title: computed(() => isEditMode.value ? t('editCustomer') : t('addCustomer')),
})

const { loading, getCustomerById, createCustomer, updateCustomer } = useCustomerApiClient()

const formData = ref<CustomerForm>({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zip: '',
  gstNumber: '',
})

const refCustomerForm = ref()

const loadCustomer = async () => {
  if (!customerId.value)
    return

  const customer = await getCustomerById(customerId.value)

  if (customer) {
    formData.value = {
      uuid: customer.uuid,
      name: customer.name ?? '',
      email: customer.email ?? '',
      phone: customer.phone ?? '',
      address: customer.address ?? '',
      city: customer.city ?? '',
      state: customer.state ?? '',
      zip: customer.zip ?? '',
      gstNumber: customer.gstNumber ?? customer.gst_number ?? '',
    }
  }
}

const saveCustomer = async () => {
  isButtonDisabled.value = true

  let response

  if (isEditMode.value)
    response = await updateCustomer({ uuid: customerId.value, ...formData.value })
  else
    response = await createCustomer(formData.value)

  if (response?.status) {
    nuxtApp.$toast.success(response.message)
    navigateTo('/customers')
  }
  else {
    isButtonDisabled.value = false
  }
}

const handleCancel = () => {
  navigateTo('/customers')
}

onMounted(async () => {
  if (isEditMode.value)
    await loadCustomer()
})
</script>

<template>
  <template v-if="loading">
    <VSkeletonLoader
      :loading="loading"
      type="heading@2, actions"
    />
  </template>
  <template v-else>
    <FormHeaderBreadcrums
      :uuid="customerId"
      page="customers"
      :action="isEditMode ? t('update') : t('create')"
    />

    <div class="tw-grid tw-grid-cols-2">
      <VCard>
        <VCardItem>
          <VForm
            ref="refCustomerForm"
            class="tw-pt-5"
            @submit.prevent="
              refCustomerForm.validate().then(function (response) {
                response.valid ? saveCustomer() : ''
              })
            "
          >
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t('name') }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VTextField
                v-model="formData.name"
                :placeholder="t('enterName')"
                :rules="[v => !!v || t('nameRequired')]"
                required
              />
            </div>

            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>{{ $t('email') }}</VLabel>
              <VTextField
                v-model="formData.email"
                :placeholder="t('enterEmail')"
                type="email"
                :rules="[v => !v || /.+@.+\..+/.test(v) || t('emailInvalid')]"
              />
            </div>

            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>{{ $t('phone') }}</VLabel>
              <VTextField
                v-model="formData.phone"
                :placeholder="t('enterPhone')"
              />
            </div>

            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>{{ $t('address') }}</VLabel>
              <VTextarea
                v-model="formData.address"
                :placeholder="t('enterAddress')"
                rows="2"
              />
            </div>

            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>{{ $t('city') }}</VLabel>
              <VTextField
                v-model="formData.city"
                :placeholder="t('enterCity')"
              />
            </div>

            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>{{ $t('state') }}</VLabel>
              <VTextField
                v-model="formData.state"
                :placeholder="t('enterState')"
              />
            </div>

            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>{{ $t('zip') }}</VLabel>
              <VTextField
                v-model="formData.zip"
                :placeholder="t('enterZip')"
              />
            </div>

            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>{{ $t('gstNumber') }}</VLabel>
              <VTextField
                v-model="formData.gstNumber"
                :placeholder="t('enterGstNumber')"
              />
            </div>

            <div class="d-flex flex-wrap gap-2 tw-justify-start tw-mt-5">
              <VBtn
                :disabled="isButtonDisabled"
                :loading="isButtonDisabled"
                color="primary"
                variant="outlined"
                @click="handleCancel"
              >
                {{ $t('cancel') }}
              </VBtn>
              <VBtn
                :disabled="isButtonDisabled"
                :loading="isButtonDisabled"
                color="primary"
                type="submit"
              >
                {{ submitLabel }}
              </VBtn>
            </div>
          </VForm>
        </VCardItem>
      </VCard>
    </div>
  </template>
</template>
