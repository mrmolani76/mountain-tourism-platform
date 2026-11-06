<script setup lang="ts">
/**
 * EQMS-style info panel for Hotel entity view tabs.
 */
const props = defineProps<{
  entityData?: Record<string, any> | null
  fields: Array<HotelColumn & { label?: string }>
  options?: Record<string, Array<HotelOption>>
}>()

const labelFor = (field: HotelColumn & { label?: string }, value: unknown) => {
  if (value === null || value === undefined || value === '')
    return '--'

  if (field.kind === 'bool')
    return value ? 'Active' : 'Inactive'

  if (field.kind === 'money')
    return formatCents(Number(value))

  if (field.kind === 'date')
    return String(value).slice(0, 10)

  if (field.kind === 'datetime')
    return String(value).replace('T', ' ').slice(0, 16)

  const options = props.options?.[field.key]
  if (options?.length) {
    const match = options.find(option => String(option.value) === String(value))

    return match?.title ?? String(value)
  }

  if (Array.isArray(value))
    return value.join(', ') || '--'

  return String(value)
}
</script>

<template>
  <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-2">
    <VCard>
      <div class="tw-mb-4 tw-text-lg tw-mt-4 tw-ml-4">
        Details
      </div>
      <VDivider
        :thickness="1"
        class="border-opacity-100"
        color="secondary"
      />
      <VList width="100%">
        <VListItem
          v-for="field in fields"
          :key="field.key"
        >
          <LabelValueDisplay
            :label="field.label || field.title"
            :value="labelFor(field, entityData?.[field.key])"
          />
        </VListItem>
      </VList>
    </VCard>
  </div>
</template>
