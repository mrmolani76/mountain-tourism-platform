<script setup lang="ts">
definePageMeta({ navActiveLink: 'hotel-hut-beds' })
useHead({ title: 'Hut bed' })

const client = useHotelApiClient()

const fields = [
  { key: 'bed_number', title: 'Bed' },
  { key: 'hut_dormitory_id', title: 'Dormitory' },
  { key: 'is_active', title: 'Status', kind: 'bool' as const },
]
</script>

<template>
  <HotelViewPage
    endpoint="hotel/hut-beds"
    list-path="/hotel/hut-beds"
    entity-label="Hut bed"
    icon="tabler-bed"
    :fields="fields"
    :title-keys="['bed_number', 'id']"
    :options-loaders="{
      hut_dormitory_id: async () => {
        const { data } = await client.listDormitories({ page: 1, page_size: 100 })
        return data.map(row => ({ value: row.id, title: row.name }))
      },
    }"
  />
</template>
