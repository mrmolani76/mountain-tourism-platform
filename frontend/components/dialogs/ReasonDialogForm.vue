<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps({
  dialogOpen: { type: Boolean, required: true },
  alertLang: { type: String, default: '' },
  dialogTitle: { type: String, default: 'confirmAction' },
  confirmButtonText: { type: String, default: 'confirm' },
  closeButtonText: { type: String, default: 'close' },
  placeholderText: { type: String, default: 'pleaseEnterReason' },
  signingDialogButtonDisabled: { type: Boolean, default: false },
  alertType: { type: String, default: 'warning' },
  headerColor: { type: String, default: 'error' },
})

const emit = defineEmits(['updatedDialogValue', 'signFormData'])

// ✅ i18n & form refs
const { t } = useI18n()
const isDialogVisible = ref(props.dialogOpen)
const refForm = ref()
const isSubmitting = ref(false)

// ✅ Form data object
const formData = ref<CoreSignatureForm>({
  reason: '',
})

// ✅ Watcher — reset form each time dialog opens
watch(
  () => props.dialogOpen,
  dialogValue => {
    isDialogVisible.value = dialogValue

    if (dialogValue) {
      formData.value = { reason: '' }
      refForm.value?.resetValidation?.()
    }
  },
)

// ✅ Close dialog
const closeDialog = () => {
  emit('updatedDialogValue', false)
  formData.value.reason = ''
}

// ✅ Submit form
const submitForm = async () => {
  if (!formData.value.reason.trim())
    return
  isSubmitting.value = true

  emit('signFormData', formData.value)

  // Small UX delay for smoothness
  setTimeout(() => {
    isSubmitting.value = false
    emit('updatedDialogValue', false)
  }, 400)
}
</script>

<template>
  <VDialog
    v-model="isDialogVisible"
    maxWidth="500"
    persistent
  >
    <VCard>
      <!-- Header -->
      <VCardTitle class="text-h6 font-weight-semibold d-flex align-center">
        {{ t(dialogTitle) }}
      </VCardTitle>

      <VDivider />

      <!-- Body -->
      <VForm
        ref="refForm"
        @submit.prevent="refForm?.validate().then(res => { if (res.valid) submitForm() })"
      >
        <VCardText>
          <VAlert
            v-if="alertLang"
            :type="alertType"
            variant="tonal"
            border="start"
            class="mb-4"
            :color="headerColor"
          >
            {{ t(alertLang) }}
          </VAlert>

          <VRow>
            <VCol cols="12">
              <VLabel>
                <span>{{ t('reason') }}</span>
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>

              <!-- Reason Textarea -->
              <AppTextarea
                v-model="formData.reason"
                :placeholder="t(placeholderText)"
                :rules="[requiredValidator, textMaxValidator(formData.reason, DELETED_REASON_MAX_TEXT_LIMIT)]"
                required
                rows="2"
                color="primary"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <!-- Footer -->
        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            :loading="isSubmitting"
            @click="closeDialog"
          >
            {{ t(closeButtonText) }}
          </VBtn>
          <VBtn
            type="submit"
            color="error"
            :loading="isSubmitting"
            :disabled="signingDialogButtonDisabled"
          >
            {{ t(confirmButtonText) }}
          </VBtn>
        </VCardText>
      </VForm>
    </VCard>
  </VDialog>
</template>
