<script setup lang="ts">
defineProps({
  modelValue: { type: Object, default: null },
  externalFilter: { type: Object, default: null },
  index: { type: String, default: null },
})

const emits = defineEmits<{
  (e: 'update:modelValue', event: any, index: number): void
  (e: 'change:modelValue'): void
}>()

const { t } = useI18n()
</script>

<template>
  <div>
    <div v-if="externalFilter.field === 'Switch'">
      <div class="tw-flex tw-justify-center">
        <div class="tw-flex tw-gap-2 tw-items-center">
          {{ externalFilter.falseText }}
          <VSwitch
            :modelValue="modelValue"
            :trueValue="externalFilter.trueValue"
            :falseValue="externalFilter.falseValue"
            @update:modelValue="emits('update:modelValue', $event, index)"
          />
          {{ externalFilter.trueText }}
        </div>
      </div>
    </div>
    <div v-else>
      <QCAutoComplete
        :modelValue="modelValue"
        :items="externalFilter.items"
        :label="t(externalFilter.label)"
        :multiple="externalFilter.multiple"
        clearable
        maxShowSelected="0"
        @change:modelValue="emits('change:modelValue')"
        @update:modelValue="emits('update:modelValue', $event, index)"
      />
    </div>
  </div>
</template>
