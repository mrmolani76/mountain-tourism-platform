<script lang="ts" setup>
const nuxtApp = useNuxtApp()
const { login } = useAuthService()
const { t } = useI18n()
const store = useAuthStore()
const { saveUser } = store

const refForm = ref()
const isDialogVisible = ref(false)

const formData = ref<AuthCredentials>({
  email: store.getUser?.email || '',
  password: '',
})

watch(() => store.logDialogState, () => {
  isDialogVisible.value = store.logDialogState
  if (store.logDialogState)
    formData.value.password = ''
})

const submitForm = async () => {
  const response = await login(formData.value)

  if (response?.status) {
    await saveUser()
    nuxtApp.$toast.success(t('login_successful'))
    store.closeLoginDialog()
  }
}
</script>

<template>
  <VDialog
    v-model="isDialogVisible"
    persistent
    maxWidth="25%"
  >
    <!-- Dialog Content -->
    <VCard :title="$t('session_timed_out_login_again')">
      <VForm
        ref="refForm"
        @submit.prevent="refForm.validate().then(function(response:{valid:boolean}) { response.valid ? submitForm() : '' })"
      >
        <VCardText>
          <VRow>
            <VCol cols="12">
              <VLabel>
                {{ $t('email') }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <AppTextField
                v-model="formData.email"
                :rules="[requiredValidator, emailValidator]"
                readonly
              />
            </VCol>
            <VCol cols="12">
              <VLabel>
                {{ $t('password') }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <AppTextField
                v-model="formData.password"
                :rules="[requiredValidator]"
                autocomplete="on"
                type="password"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end flex-wrap gap-3 tw-mt-4">
          <VBtn type="submit">
            {{ $t('login') }}
          </VBtn>
        </VCardText>
      </VForm>
    </VCard>
  </VDialog>
</template>
