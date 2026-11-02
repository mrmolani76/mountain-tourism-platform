<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'
import { useUserApiClient } from '@/composables/api-clients/useUserApiClient'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

definePageMeta({
  layout: 'blank',
  public: true,
  unauthenticatedOnly: true,
})

const { t } = useI18n()
const { login } = useUserApiClient()
const store = authStore()
const route = useRoute()

useHead({
  title: t('login'),
})

const refForm = ref<VForm | null>(null)
const isButtonDisabled = ref(false)
const isPasswordVisible = ref(false)

const form = ref({
  email: '',
  password: '',
})

const onSubmit = async () => {
  const result = await refForm.value?.validate()

  if (!result?.valid)
    return

  isButtonDisabled.value = true

  const response = await login({
    email: form.value.email.trim().toLowerCase(),
    password: form.value.password,
  })

  if (response?.status) {
    const user = await store.saveUser()

    if (user) {
      const target = route.query.to ? String(route.query.to) : '/'
      const safeTarget = target === '/login' || target.startsWith('/login?') ? '/' : target

      await navigateTo(safeTarget, { replace: true })
    }
  }

  isButtonDisabled.value = false
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- Auth card -->
      <VCard
        class="auth-card pa-4"
        max-width="448"
      >
        <VCardItem class="justify-center">
          <template #prepend>
            <div class="d-flex align-center gap-x-2">
              <VIcon
                icon="tabler-mountain"
                size="34"
                color="primary"
              />
              <span class="text-h5 text-capitalize">
                {{ themeConfig.app.title }}
              </span>
            </div>
          </template>
        </VCardItem>

        <VCardText class="pt-1">
          <h4 class="text-h4 mb-1">
            {{ t('welcomeTo') }} <span class="text-capitalize">{{ themeConfig.app.title }}</span>!
          </h4>
          <p class="mb-0">
            {{ t('pleaseSignInToYourAccount') }}
          </p>
        </VCardText>

        <VCardText>
          <VForm
            ref="refForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="form.email"
                  autofocus
                  :label="t('email')"
                  type="email"
                  autocomplete="username"
                  :placeholder="t('email')"
                  :rules="[requiredValidator, emailValidator]"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="form.password"
                  :label="t('password')"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator]"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <VBtn
                  class="mt-6"
                  :disabled="isButtonDisabled"
                  :loading="isButtonDisabled"
                  block
                  type="submit"
                >
                  {{ t('login') }}
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core/scss/template/pages/page-auth";
</style>
