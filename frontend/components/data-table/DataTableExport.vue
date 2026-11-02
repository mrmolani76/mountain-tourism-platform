<script setup lang="ts">
const props = defineProps({
  exportColumns: { type: Array, default() { return [] } },
  dialog: { type: Boolean, default: false },
  showColumnField: { type: Boolean, default: true },
  currentColumns: { type: Array, default() { return [] } },
})

const emits = defineEmits<{
  (e: 'updatedDialogValue', isOpen: boolean): void
  (e: 'submitExportListingForm', exportFormData: Record<string, any>): void
}>()

const dialogOpen = ref(props.dialog)

const exportFormData = ref({
  exportFormat: 'xlsx',
  applyFilters: false,
  columns: props.currentColumns,
})

const isAllSelected = computed(() => {
  return props.exportColumns.length === exportFormData.value.columns.length;
})

const selectAllToggle = (isSelected: boolean) => {
  if (isSelected)
    exportFormData.value.columns = props.exportColumns.filter(col => col.disabled).map(col => col.key)
  else
    exportFormData.value.columns = props.exportColumns.map(col => col.key)
}

watch(() => props.dialog, dialogValue => {
  if (dialogValue)
    exportFormData.value.columns = props.currentColumns

  dialogOpen.value = dialogValue
})
</script>

<template>
  <VDialog
    v-model="dialogOpen"
    persistent
    :width="showColumnField ? '40%' : '20%'"
    :update:modelValue="emits('updatedDialogValue', dialogOpen)"
  >
    <DialogCloseBtn @click="dialogOpen = !dialogOpen" />
    <VCard>
      <VToolbar color="blue-darken-3">
        <VToolbarTitle>{{ $t('exportListing') }}</VToolbarTitle>
      </VToolbar>
      <VDivider />
      <VCardText>
        <div :class="`tw-grid ${showColumnField ? 'tw-grid-cols-2' : 'tw-grid-cols-1'} tw-gap-4`">
          <div v-if="showColumnField">
            <div class="tw-text-lg tw-mb-2">
              {{ $t('columns') }}
              <VBtn
                size="small"
                variant="link"
                @click="selectAllToggle(isAllSelected)"
              >
                {{ isAllSelected ? $t('deSelectAll') : $t('selectAll') }}
              </VBtn>
              <VBtn
                size="small"
                variant="link"
                @click="exportFormData.columns = props.currentColumns"
              >
                {{ $t('selectCurrent') }}
              </VBtn>
            </div>
            <div class="tw-h-80 tw-overflow-y-scroll tw-border tw-p-2">
              <template
                v-for="column in exportColumns"
                :key="column.key"
              >
                <VCheckbox
                  v-model="exportFormData.columns"
                  :value="column.key"
                  :label="column.title"
                  :disabled="column.disabled"
                />
              </template>
            </div>
          </div>
          <div>
            <div class="tw-text-lg">
              {{ $t('exportType') }}
            </div>
            <VRadioGroup
              v-model="exportFormData.exportFormat"
              inline
              class="tw-mb-2"
            >
              <VRadio
                :label="$t('xls')"
                value="xlsx"
              />
              <VRadio
                :label="$t('csv')"
                value="csv"
              />
            </VRadioGroup>
            <VCheckbox
              v-model="exportFormData.applyFilters"
              :label="$t('applyListingFiltersOnExport')"
            />
          </div>
        </div>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          color="blue-darken-1"
          variant="tonal"
          @click="dialogOpen = false"
        >
          {{ $t('cancel') }}
        </VBtn>
        <VBtn
          color="primary"
          variant="tonal"
          @click="emits('submitExportListingForm', exportFormData); dialogOpen = false"
        >
          {{ $t("export") }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
