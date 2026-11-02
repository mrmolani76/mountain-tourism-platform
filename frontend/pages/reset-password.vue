<script setup>
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw';
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw';
import logoUrl from '@images/logo.png';
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { themeConfig } from '@themeConfig';

definePageMeta({
  layout: 'blank',
  public: true,
})

const nuxtApp = useNuxtApp();
const route = useRoute();
const { resetPassword } = useUserApiClient();

const form = ref({
  email: '',
  password: '',
  confirmPassword: '',
  otp: '',
})

const hideOtpFields = !!(route.query.key);

if (route.query.key) {
  const json = JSON.parse(atob(route.query.key));

  form.value.email = json[0];
  form.value.otp = json[1];
}
else if (route.query.email) {
  form.value.email = atob(route.query.email);
}

const refForm = ref();
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const disabledSubmitBtn = ref(false);
const progress = ref(0)

const passwordRegex = ref({
  hasNumber: /\d/,
  hasUppercase: /[A-Z]/,
  hasLowercase: /[a-z]/,
  hasSpecial: /[^A-Z0-9]/i,
});

const passwordLength = {
  isLong: 8,
  isExcellent: 16,
};

const isOtpInserted = ref(false)

const onFinish = () => {
  isOtpInserted.value = true
  setTimeout(() => {
    isOtpInserted.value = false
    router.push('/')
  }, 2000)
}

const submitForm = async () => {
  disabledSubmitBtn.value = true;

  const response = await resetPassword(form.value);

  if (response.status) {
    nuxtApp.$toast.success(response.message, {
      onClose: () => {
        navigateTo({
          path: '/login',
          query: {
            sso: 0,
          },
        })
      },
    })
  }
  else {
    disabledSubmitBtn.value = false;
  }
}

const checkPasswordStrength = () => {
  let tempProgress = 0;
  const pass = form.value.password.toString();
  for (const element in passwordRegex.value) {
    if (passwordRegex.value[element].test(pass))
      tempProgress += 1;
  }

  if (pass.length >= passwordLength.isLong)
    tempProgress += 1;

  if (pass.length >= passwordLength.isExcellent)
    tempProgress += 1;

  progress.value = tempProgress;
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

      <!-- 👉 Auth Card -->
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
          <VCardTitle class="font-weight-bold text-capitalize text-h3 py-1">
            {{ themeConfig.app.title }}
          </VCardTitle>
        </VCardItem>

        <VCardText class="pt-2">
          <h4 class="text-h4">
            {{ $t('resetPassword') }}🔒
          </h4>

          <div>
            {{ $t('passwordPolicyExplain') }}
          </div>
        </VCardText>

        <VCardText>
          <VForm
            ref="refForm"
            @submit.prevent="refForm.validate().then(function(response) { response.valid ? submitForm() : '' })"
          >
            <VRow>
              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.password"
                  autofocus
                  :label="$t('newPassword')"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :appendInnerIcon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, validatePassword(form.password)]"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  @update:model-value="checkPasswordStrength"
                />
              </VCol>

              <!-- Confirm Password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.confirmPassword"
                  :label="$t('confirmPassword')"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :appendInnerIcon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, confirmedValidator(form.confirmPassword, form.password)]"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
                <PasswordProgressBar :progress="progress" />
              </VCol>

              <!-- otp -->
              <VCol
                v-if="!hideOtpFields"
                cols="12"
              >
                <p class="mb-2">
                  {{ $t('otpFormHelpText1') }}
                </p>
                <h6 class="text-base font-weight-medium mt-3">
                  {{ $t('otpFormHelpText2') }}
                </h6>
                <VOtpInput
                  v-model="form.otp"
                  :disabled="isOtpInserted"
                  type="number"
                  class="pa-0"
                  @finish="onFinish"
                />
              </VCol>

              <!-- reset password -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :disabled="disabledSubmitBtn"
                  :loading="disabledSubmitBtn"
                >
                  {{ $t('setNewPassword') }}
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <NuxtLink
                  to="/login"
                  class="d-flex align-center justify-center"
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
@use "@core/scss/template/pages/page-auth";
</style>
