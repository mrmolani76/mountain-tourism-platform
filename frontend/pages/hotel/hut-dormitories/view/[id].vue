<script setup lang="ts">
definePageMeta({ navActiveLink: 'hotel-hut-dormitories' })
useHead({ title: 'Hut dormitory' })

const client = useHotelApiClient()

const fields = [
  { key: 'name', title: 'Name' },
  { key: 'building_id', title: 'Building' },
  { key: 'room_type_id', title: 'Room type' },
  { key: 'is_active', title: 'Status', kind: 'bool' as const },
]
</script>

<template>
  <HotelViewPage
    endpoint="hotel/hut-dormitories"
    list-path="/hotel/hut-dormitories"
    entity-label="Hut dormitory"
    icon="tabler-home"
    :fields="fields"
    :options-loaders="{
      building_id: () => client.listBuildingOptions(),
      room_type_id: async () => {
        const { data } = await client.listRoomTypes({ page: 1, page_size: 100 })
        return data.map(row => ({ value: row.id, title: row.name }))
      },
    }"
  />
</template>
