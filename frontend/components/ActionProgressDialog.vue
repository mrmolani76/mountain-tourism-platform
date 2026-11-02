<script setup lang="ts">
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  progressPercentage: { type: Number, default: 0 },
  prependProgressText: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const isDialogVisible = ref(props.modelValue)
const progressTextArr = ref([]);

watch(() => props.modelValue, value => {
  if (value)
    progressTextArr.value = [];

  isDialogVisible.value = value;
})

const closeDialog = () => {
  isDialogVisible.value = false;
  emit('update:modelValue', isDialogVisible.value);
}

watch(() => props.prependProgressText, (value: string) => {
  progressTextArr.value.unshift(value);
})

const exportContent = () => {
  const link = document.createElement('a');
  const content = structuredClone(toRaw(progressTextArr.value));
  const file = new Blob([content.join('\n')], { type: 'text/plain' });

  link.href = URL.createObjectURL(file);
  link.download = 'Bulk Action Export.txt';
  link.click();
  URL.revokeObjectURL(link.href);
}
</script>

<template>
  <div>
    <VDialog
      v-model="isDialogVisible"
      width="40%"
      persistent
      fixed
      @update:model-value="emit('update:modelValue', isDialogVisible)"
    >
      <DialogCloseBtn @click="closeDialog" />

      <VCard>
        <VCardTitle class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-pt-4">
          <span>{{ $t('progress') }}</span>
          <span class="tw-font-semibold tw-text-sm">{{ progressPercentage }}%</span>
        </VCardTitle>
        <div class="tw-mx-4">
          <VProgressLinear
            :modelValue="progressPercentage"
            :color="progressPercentage === 100 ? 'success' : 'primary'"
            height="12"
            rounded
            striped
            class="tw-mb-4"
          />
        </div>

        <VCardText>
          <div class="tw-border-2 tw-rounded-lg tw-h-48 tw-overflow-y-auto tw-p-2">
            <VList class="tw-m-0 tw-p-0">
              <VListItem
                v-for="(text, index) in progressTextArr"
                :key="index"
                :variant="index === 0 ? 'tonal' : 'text'"
                class="tw-px-2 tw-py-1 tw-text-sm"
              >
                <span>{{ text }}</span>
              </VListItem>
            </VList>
          </div>

          <div class="tw-flex tw-mt-4 tw-justify-end">
            <VBtn
              :disabled="progressPercentage != 100"
              @click="exportContent"
            >
              {{ $t('export') }}
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>
