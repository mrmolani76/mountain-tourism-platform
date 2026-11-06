<script setup lang="ts">
useHead({ title: 'Seasons' })

const fields: Array<HotelFormField> = [
  {
    key: 'kind',
    label: 'Kind',
    type: 'select',
    required: true,
    options: [
      { value: 'winter', title: 'Winter' },
      { value: 'summer', title: 'Summer' },
      { value: 'closed', title: 'Closed' },
    ],
  },
  { key: 'starts_on', label: 'Starts on', type: 'date', required: true },
  { key: 'ends_on', label: 'Ends on', type: 'date', required: true },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <ResourceCrudPage
    title="Seasons"
    subtitle="Winter, summer or closed date ranges. Prices can differ by season."
    endpoint="seasons"
    :fields="fields"
    :defaults="{ kind: 'winter', is_active: true }"
    add-label="New season"
  />
</template>
