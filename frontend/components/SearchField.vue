<script setup lang="ts">
import { themeConfig } from '@themeConfig'

const props = defineProps({
  modelValue: String || Array || Object || Number,
  fieldData: { type: Array, default() { return [] } },
  column: String,
  columnKey: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue', 'change:modelValue'])

const { t } = useI18n()

const tempModelValue = ref(props.modelValue)
const tempModelValueOld = ref(props.modelValue)

const languages = themeConfig.app.i18n.langConfig.map(lang => ({
  ...lang,
  label: t(lang.label),
}))

// ----------------- Computed Properties ----------------
const maxDate = computed(() => {
  const futureDateColumn = ['dateCreated', 'dateModified', 'updatedAt', 'createdAt', 'deletedOn', 'uploadedOn', 'signedOn', 'printedOn']
  if (futureDateColumn.includes(props.columnKey))
    return new Date()
})

// ----------------- Methods ----------------------------
const fieldValueUpdated = () => {
  if (tempModelValue.value !== tempModelValueOld.value) {
    emit('change:modelValue')
    emit('update:modelValue', tempModelValue.value)
    tempModelValueOld.value = tempModelValue.value
  }
}

const dateRangeselected = (selectedDates: Array<string>, dateStr: string) => {
  if (selectedDates.length === 2 && tempModelValue.value && (dateStr?.length || dateStr.includes('to'))) {
    tempModelValue.value = dateStr
    fieldValueUpdated()
  }
  else {
    tempModelValue.value = ''
  }
}

// ----------------- Watchers ---------------------------
watch(() => props.modelValue, val => {
  tempModelValue.value = val
})
</script>

<template>
  <template v-if="column === 'Text'">
    <AppTextField
      v-model="tempModelValue"
      clearable
      @update:modelValue="fieldValueUpdated"
    />
  </template>
  <template v-else-if="column === 'Dropdown'">
    <template v-if="columnKey === 'language'">
      <AutoComplete
        v-model="tempModelValue"
        :items="languages"
        itemTitle="label"
        itemValue="i18nLang"
        itemKey="i18nLang"
        clearable
        multiple
        maxShowSelected="1"
        class="tw-normal-case"
        @click:clear="fieldValueUpdated"
        @update:menu="(val) => { if (!val) fieldValueUpdated(); }"
      />
    </template>
    <template v-else>
      <AutoComplete
        v-model="tempModelValue"
        :items="fieldData"
        itemTitle="title"
        itemValue="value"
        class="tw-normal-case"
        multiple
        clearable
        maxShowSelected="1"
        @click:clear="fieldValueUpdated"
        @update:menu="(val) => { if (!val) fieldValueUpdated(); }"
      />
    </template>
  </template>
  <template v-else-if="column === 'Numeric'">
    <NumberFilter
      v-model="tempModelValue"
      clearable
      @update:modelValue="fieldValueUpdated"
      @click:clear="fieldValueUpdated"
    />
  </template>
  <template v-else-if="column === 'Date'">
    <AppDateTimePicker
      v-model="tempModelValue"
      :config="{ mode: 'range', maxDate }"
      clearable
      @onClose="dateRangeselected"
      @click:clear="fieldValueUpdated"
    />
  </template>
</template>
