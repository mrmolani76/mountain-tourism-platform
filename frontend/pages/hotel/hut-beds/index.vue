<script setup lang="ts">
useHead({ title: 'Hut beds' })

const client = useHotelApiClient()

const fields: Array<HotelFormField> = [
  {
    key: 'hut_dormitory_id',
    label: 'Dormitory',
    type: 'select',
    required: true,
    optionsLoader: async () => {
      const { data } = await client.listDormitories({ page: 1, page_size: 100 })

      return data.map(row => ({ value: row.id, title: row.name }))
    },
  },
  { key: 'bed_number', label: 'Bed number', required: true },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <HotelListingPage
    resource="hut-beds"
    title="Hut beds"
    subtitle="Each bed is reservable on its own date range."
    endpoint="hotel/hut-beds"
    view-path="/hotel/hut-beds"
    :fields="fields"
    add-label="New bed"
  />
</template>
