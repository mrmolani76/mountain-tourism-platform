<script setup lang="ts">
definePageMeta({ navActiveLink: 'hotel-rooms' })
useHead({ title: 'Room' })

const client = useHotelApiClient()

const fields = [
  { key: 'room_number', title: 'Number' },
  { key: 'name', title: 'Name' },
  { key: 'building_id', title: 'Building' },
  { key: 'room_type_id', title: 'Room type' },
  { key: 'accommodation_type', title: 'Type' },
  { key: 'is_active', title: 'Status', kind: 'bool' as const },
]
</script>

<template>
  <HotelViewPage
    endpoint="hotel/rooms"
    list-path="/hotel/rooms"
    entity-label="Room"
    icon="tabler-door"
    :fields="fields"
    :title-keys="['room_number', 'name', 'id']"
    :options-loaders="{
      building_id: () => client.listBuildingOptions(),
      room_type_id: async () => {
        const { data } = await client.listRoomTypes({ page: 1, page_size: 100 })
        return data.map(row => ({ value: row.id, title: row.name }))
      },
    }"
  />
</template>
