<script setup>
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw';
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw';
import logoUrl from '@images/logo.png';
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { themeConfig } from '@themeConfig';

definePageMeta({
  layout: 'blank',
  public: true,
  unauthenticatedOnly: true,
})

const nuxtApp = useNuxtApp();
const { sendResetPasswordMail } = useUserApiClient();

const refForm = ref();
const email = ref('')
const isButtonDisabled = ref(false);

const submitForm = async () => {
  isButtonDisabled.value = true;

  const response = await sendResetPasswordMail({ email: email.value })

  isButtonDisabled.value = false;

  if (!response?.status)
    return;

  nuxtApp.$toast.info(response.message, {
    onClose: () => {
      navigateTo({
        path: '/reset-password',
        query: {
          email: btoa(email.value),
        },
      });
    },
  });
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth card -->
      <VCard
        class="auth-card pa-4"
        maxWidth="448"
      >
        <VCardItem class="justify-center">
          <template #prepend>
            <div class="d-flex">
              <img
                :src="logoUrl"
                width="200"
              >
            </div>
          </template>
        </VCardItem>

        <VCardText class="pt-2">
          <h4 class="text-h4 mb-1">
            {{ $t('forgetPassword') }}? 🔒
          </h4>
          <p class="mb-0">
            {{ $t('enterEmailWillSendResetInstructions') }}
          </p>
        </VCardText>

        <VCardText>
          <VForm
            ref="refForm"
            @submit.prevent="refForm.validate().then(function(response) { response.valid ? submitForm() : '' })"
          >
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  autofocus
                  :label="$t('email')"
                  type="email"
                  :rules="[requiredValidator, emailValidator]"
                />
              </VCol>

              <!-- reset password -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :disabled="isButtonDisabled"
                  :loading="isButtonDisabled"
                >
                  {{ $t('sentOtp') }}
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <NuxtLink
                  class="d-flex align-center justify-center"
                  to="/login"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    class="flip-in-rtl"
                  />
                  <span>{{ $t('backToLogin') }}</span>
                </NuxtLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core/scss/template/pages/page-auth.scss";
</style>
