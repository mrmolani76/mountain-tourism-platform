<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'

definePageMeta({
  layout: 'blank',
})

const { changeOwnPassword } = useAuthService()
const store = useAuthStore()
const formRef = ref<VForm>()
const loading = ref(false)
const error = ref('')
const form = ref({
  new_password: '',
  new_password_confirmation: '',
})

const passwordConfirmationRule = (value: string) =>
  value === form.value.new_password || 'Passwords do not match.'

const passwordLengthRule = (value: string) =>
  String(value).length >= 8 || 'Use at least 8 characters.'

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid || loading.value)
    return

  loading.value = true
  error.value = ''

  try {
    const response = await changeOwnPassword(form.value)
    if (!response?.status) {
      error.value = response?.message || 'Password could not be changed. Retry the request.'
      return
    }

    await store.saveUser()
    await navigateTo('/', { replace: true })
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard
      class="pa-4"
      max-width="480"
      width="100%"
    >
      <VCardTitle class="text-h5">
        Set a new password
      </VCardTitle>
      <VCardText>
        <p class="text-body-2 text-medium-emphasis">
          Your Owner account uses a temporary password. Set a new password to continue.
        </p>
        <VAlert
          v-if="error"
          type="error"
          variant="tonal"
          class="mb-4"
        >
          {{ error }}
        </VAlert>
        <VForm
          ref="formRef"
          @submit.prevent="submit"
        >
          <VTextField
            v-model="form.new_password"
            label="New password"
            type="password"
            autocomplete="new-password"
            :rules="[requiredValidator, passwordLengthRule]"
          />
          <VTextField
            v-model="form.new_password_confirmation"
            label="Confirm new password"
            type="password"
            autocomplete="new-password"
            :rules="[requiredValidator, passwordConfirmationRule]"
          />
          <VBtn
            type="submit"
            block
            class="mt-4"
            :loading="loading"
            :disabled="loading"
          >
            Save password
          </VBtn>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
