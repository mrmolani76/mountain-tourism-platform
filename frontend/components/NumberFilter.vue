<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ operation: '', number: '' }),
  },
})

const emit = defineEmits(['update:modelValue'])

const fieldList = [
  { title: '<', value: '<' },
  { title: '>', value: '>' },
  { title: '=', value: '=' },
  { title: '≤', value: '<=' },
  { title: '≥', value: '>=' },
]

const fieldData = ref({
  operation: props.modelValue.operation,
  number: props.modelValue.number,
})

const fieldString = computed(() => {
  if (fieldData.value.operation && fieldData.value.number)
    return `Value ${fieldData.value.operation} ${fieldData.value.number}`

  return ''
})

const updateValue = () => {
  if (fieldData.value.number && fieldData.value.operation)
    emit('update:modelValue', { operation: fieldData.value.operation, number: fieldData.value.number })
}

const clearFilter = () => {
  fieldData.value = {
    operation: '',
    number: '',
  }
  emit('update:modelValue', '')
}

watch(() => props.modelValue, () => {
  if (!props.modelValue || (props.modelValue.operation === '' && props.modelValue.number === '')) {
    fieldData.value = {
      operation: '',
      number: '',
    }
  }
  else {
    fieldData.value = { ...props.modelValue }
  }
})
</script>

<template>
  <VMenu :closeOnContentClick="false">
    <template #activator="{ props }">
      <VTextField
        :modelValue="fieldString"
        readonly
        clearable
        v-bind="props"
        @click:clear="clearFilter"
      />
    </template>

    <VCard maxWidth="300">
      <VCardText>
        <div class="tw-flex tw-gap-1">
          <VAutocomplete
            v-model="fieldData.operation"
            :items="fieldList"
            class="tw-w-24"
            @update:modelValue="updateValue"
          />
          <AppTextField
            v-model="fieldData.number"
            type="number"
            :min="0"
            class="tw-w-24"
            @update:modelValue="updateValue"
          />
        </div>
      </VCardText>
    </VCard>
  </VMenu>
</template>
