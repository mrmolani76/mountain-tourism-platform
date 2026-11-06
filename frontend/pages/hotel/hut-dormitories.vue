<script setup lang="ts">
useHead({ title: 'Hut dormitories' })

const client = useHotelApiClient()

const columns: Array<HotelColumn> = [
  { key: 'name', title: 'Name' },
  { key: 'building_id', title: 'Building' },
  { key: 'room_type_id', title: 'Room type' },
  { key: 'is_active', title: 'Status' },
]

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
  <HotelCrudPage
    title="Hut dormitories"
    subtitle="Dormitory blocks inside a hut. Beds are booked individually."
    endpoint="hotel/hut-dormitories"
    :columns="columns"
    :fields="fields"
    add-label="New dormitory"
  />
</template>
