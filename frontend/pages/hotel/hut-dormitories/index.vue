<script setup lang="ts">
useHead({ title: 'Hut dormitories' })

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
    hint: 'Beds inherit this room type price',
    optionsLoader: async () => {
      const { data } = await client.listRoomTypes({ page: 1, page_size: 100 })

      return data.map(row => ({ value: row.id, title: row.name }))
    },
  },
  { key: 'name', label: 'Name', required: true },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <HotelListingPage
    resource="hut-dormitories"
    title="Hut dormitories"
    subtitle="Dormitory blocks inside a hut. Beds are booked individually."
    endpoint="hotel/hut-dormitories"
    view-path="/hotel/hut-dormitories"
    :fields="fields"
    add-label="New dormitory"
  />
</template>
