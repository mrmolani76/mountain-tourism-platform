<script setup>
import { onMounted, ref } from 'vue'

const isConfirmVisible = ref(false)
const title = ref('')
const message = ref('')
const actionLabel = ref('')

let resolveCallback;

const onConfirm = () => {
  isConfirmVisible.value = false
  resolveCallback()
}

onMounted(() => {
  const showAlertDialog = (aTitle, aMessage, aActionLabel) => {
    title.value = aTitle || 'title';
    message.value = aMessage || 'message';
    actionLabel.value = aActionLabel || 'ok';
    isConfirmVisible.value = true

    return new Promise(resolve => {
      resolveCallback = resolve
    })
  }

  window.showAlertDialog = showAlertDialog
})
</script>

<template>
  <VDialog
    v-model="isConfirmVisible"
    persistent
    class="v-dialog-sm"
  >
    <VCard :title="$t(title)">
      <VCardText>
        <div v-html="$t(message)" />
      </VCardText>

      <VCardText class="d-flex justify-end gap-3 flex-wrap">
        <VBtn @click="onConfirm">
          {{ $t(actionLabel) }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
