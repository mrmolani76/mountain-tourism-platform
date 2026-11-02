<script lang="ts" setup>
const props = defineProps({
  dialogOpen: { type: Boolean, default: false },
  signingDialogButtonDisabled: { type: Boolean, default: false },
  title: { type: String, default: '' },
  askRemark: { type: Boolean, default: true },
  reason: { type: Boolean, default: false }, // Signing form contains two fields reason or remarks by default the remark is default to override send this prop true
  alertLang: { type: String, default: '' },
});

const emit = defineEmits(['updatedDialogValue', 'signFormData'])

const emailLabel = authStore().user?.email;
const refForm = ref();
const isDialogVisible = ref(props.dialogOpen)

const formData = ref<CoreSignatureForm>({
  password: '',
});

// const adEnabled = ref(authStore().getAdConfig.adEnabled);
const adEnabled = ref(false);

watch(() => props.dialogOpen, dialogValue => {
  isDialogVisible.value = dialogValue;
});

const submitForm = async () => {
//   if (adEnabled.value) {
//     const msal = new useMsal();

  //     await msal.initialize();

  //     const response = await msal.loginPopup();

  //     formData.value.is_ad_azure_digital_sign_enabled = true;
  //     formData.value.user_sign_token = response.id_token;
  //   }

  const tempFormData = JSON.parse(JSON.stringify(formData.value));

  emit('signFormData', tempFormData);
}

const resetAndCloseForm = () => {
  isDialogVisible.value = false
  formData.value = {
    password: '',
  }
}

defineExpose({ resetAndCloseForm });
</script>

<template>
  <VDialog
    v-model="isDialogVisible"
    maxWidth="25%"
    :update:modelValue="emit('updatedDialogValue', isDialogVisible)"
  >
    <!-- Dialog Content -->
    <VCard :title="title">
      <div
        v-if="props.alertLang"
        class="tw-mt-3 tw-ml-6 tw-mr-6"
      >
        <VAlert
          density="compact"
          color="warning"
          variant="tonal"
          class="tw-font-normal"
        >
          <VIcon
            size="22"
            icon="tabler-alert-triangle-filled"
            style="opacity: 1;"
          />
          {{ props.alertLang }}
        </VAlert>
      </div>
      <VForm
        ref="refForm"
        @submit.prevent="refForm.validate().then(function (response) { response.valid ? submitForm() : '' })"
      >
        <VCardText>
          <VRow>
            <template v-if="!adEnabled">
              <VCol cols="12">
                <VLabel>
                  {{ $t('email') }}
                </VLabel>
                <div>{{ emailLabel }}</div>
              </VCol>
              <VCol cols="12">
                <VLabel>
                  {{ $t('password') }}
                  <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
                </VLabel>
                <AppTextField
                  v-model="formData.password"
                  :rules="[requiredValidator]"
                  :required="true"
                  autocomplete="on"
                  type="password"
                />
              </VCol>
            </template>
            <VCol
              v-if="askRemark"
              cols="12"
            >
              <VLabel>
                <span v-if="reason">{{ $t('reason') }}</span><span v-else>{{ $t('remark') }}</span>
                <span
                  v-if="reason"
                  class=" tw-text-red-500 tw-font-bold tw-ms-2"
                >*</span>
              </VLabel>
              <AppTextarea
                v-if="reason"
                v-model="formData.reason"
                :rules="[requiredValidator, textMaxValidator(formData.reason, DELETED_REASON_MAX_TEXT_LIMIT)]"
                :required="true"
                rows="2"
              />
              <AppTextarea
                v-else
                v-model="formData.remark"
                rows="2"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end flex-wrap gap-3 tw-mt-4">
          <VBtn
            variant="tonal"
            color="secondary"
            :disabled="signingDialogButtonDisabled"
            :loading="signingDialogButtonDisabled"
            @click="refForm.reset(); isDialogVisible = false"
          >
            {{ $t('cancel') }}
          </VBtn>
          <VBtn
            type="submit"
            :disabled="signingDialogButtonDisabled"
            :loading="signingDialogButtonDisabled"
          >
            {{ $t('sign') }}
          </VBtn>
        </VCardText>
      </VForm>
    </VCard>
  </VDialog>
</template>
