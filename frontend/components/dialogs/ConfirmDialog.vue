<script setup lang="ts">
const isDialogVisible = ref(false)
const confirmationQuestion = ref('')
const confirmTitle = ref()
const cancelTitle = ref()
let resolveCallback: ((value: boolean) => void) | null = null

const onConfirmation = () => {
  isDialogVisible.value = false
  resolveCallback?.(true)
}

const onCancel = () => {
  isDialogVisible.value = false
  resolveCallback?.(false)
}

onMounted(() => {
  const showConfirmDialog = (
    message: string = 'areYouSure',
    confirm: string = 'confirm',
    cancel: string = 'cancel',
  ) => {
    confirmationQuestion.value = message
    confirmTitle.value = confirm
    cancelTitle.value = cancel
    isDialogVisible.value = true

    return new Promise<boolean>(resolve => {
      resolveCallback = resolve
    })
  }

  (window as any).showConfirmDialog = showConfirmDialog
})
</script>

<template>
  <!-- 👉 Confirm Dialog -->
  <VDialog
    maxWidth="500"
    :modelValue="isDialogVisible"
    persistent
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VBtn
          icon
          variant="outlined"
          color="warning"
          class="my-4"
          style=" block-size: 88px;inline-size: 88px; pointer-events: none;"
        >
          <span class="text-5xl">!</span>
        </VBtn>

        <h6 class="text-lg font-weight-medium">
          {{ $t(confirmationQuestion) }}
        </h6>
      </VCardText>

      <VCardText class="d-flex align-center justify-center gap-2">
        <VBtn
          variant="elevated"
          @click="onConfirmation"
        >
          {{ $t(confirmTitle) }}
        </VBtn>

        <VBtn
          color="secondary"
          variant="tonal"
          @click="onCancel"
        >
          {{ $t(cancelTitle) }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
