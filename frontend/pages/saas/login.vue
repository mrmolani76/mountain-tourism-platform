<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'

definePageMeta({
  layout: 'blank',
  public: true,
})

useHead({ title: 'Super Admin sign in' })

const auth = useSaasAuth()
const route = useRoute()
const formRef = ref<VForm>()
const loading = ref(false)
const errorMessage = ref('')
const showPassword = ref(false)
const form = ref({ email: '', password: '' })

const redirectAuthenticatedUser = async () => {
  const returnPath = typeof route.query.to === 'string' ? route.query.to : ''
  const target = returnPath === '/saas' || returnPath.startsWith('/saas/')
    ? returnPath
    : '/saas'

  await navigateTo(target, { replace: true })
}

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid || loading.value)
    return

  loading.value = true
  errorMessage.value = ''

  try {
    const user = await auth.login(form.value.email.trim().toLowerCase(), form.value.password)
    form.value.password = ''

    if (!user) {
      errorMessage.value = 'Sign-in failed. Check your credentials or try again.'
      return
    }

    await redirectAuthenticatedUser()
  }
  finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    await auth.fetchUser()
    if (auth.isAuthenticated.value)
      await redirectAuthenticatedUser()
  }
  catch {
    errorMessage.value = 'Could not verify your current session. You can still sign in.'
  }
})
</script>

<template>
  <div class="d-flex align-center justify-center min-h-screen pa-4">
    <VCard
      max-width="440"
      width="100%"
      class="pa-4 pa-sm-6"
    >
      <VCardTitle class="d-flex align-center ga-3 px-0">
        <VIcon
          icon="tabler-mountain"
          color="primary"
          size="30"
        />
        Mountain Tourism
      </VCardTitle>
      <VCardText class="px-0 pb-2 text-medium-emphasis">
        Platform administrator sign in
      </VCardText>

      <VAlert
        v-if="errorMessage"
        type="error"
        variant="tonal"
        class="mb-4"
      >
        {{ errorMessage }}
      </VAlert>

      <VForm
        ref="formRef"
        @submit.prevent="submit"
      >
        <VTextField
          v-model="form.email"
          label="Email"
          type="email"
          autofocus
          autocomplete="username"
          :rules="[requiredValidator, emailValidator]"
          class="mb-2"
        />
        <VTextField
          v-model="form.password"
          label="Password"
          :type="showPassword ? 'text' : 'password'"
          autocomplete="current-password"
          :rules="[requiredValidator]"
          :append-inner-icon="showPassword ? 'tabler-eye-off' : 'tabler-eye'"
          @click:append-inner="showPassword = !showPassword"
          class="mb-4"
        />
        <VBtn
          type="submit"
          block
          :loading="loading"
          :disabled="loading"
        >
          Sign in
        </VBtn>
      </VForm>
    </VCard>
  </div>
</template>
