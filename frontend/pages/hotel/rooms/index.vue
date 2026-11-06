<script setup lang="ts">
useHead({ title: 'Rooms and chalets' })

const client = useHotelApiClient()

const fields: Array<HotelFormField> = [
  {
    key: 'building_id',
    label: 'Building',
    type: 'select',
    required: true,
    optionsLoader: () => client.listBuildingOptions(),
  },
  {
    key: 'room_type_id',
    label: 'Room type',
    type: 'select',
    required: true,
    optionsLoader: async () => {
      const { data } = await client.listRoomTypes({ page: 1, page_size: 100 })

      return data.map(row => ({ value: row.id, title: row.name }))
    },
  },
  { key: 'room_number', label: 'Room number', required: true },
  { key: 'name', label: 'Name' },
  {
    key: 'accommodation_type',
    label: 'Type',
    type: 'select',
    options: [
      { value: 'room', title: 'Room' },
      { value: 'chalet', title: 'Chalet' },
    ],
  },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <HotelListingPage
    resource="rooms"
    title="Rooms and chalets"
    subtitle="Rooms belong to a building and a room type. A chalet is booked exactly like a room."
    endpoint="hotel/rooms"
    view-path="/hotel/rooms"
    :fields="fields"
    :defaults="{ accommodation_type: 'room' }"
    add-label="New room"
  />
</template>
