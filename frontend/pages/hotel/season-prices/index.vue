<script setup lang="ts">
useHead({ title: 'Season prices' })

const client = useHotelApiClient()

const columns: Array<HotelColumn> = [
  { key: 'room_type_id', title: 'Room type' },
  { key: 'season_id', title: 'Season' },
  { key: 'amount_cents', title: 'Price per night', kind: 'money', align: 'end' },
]

const fields: Array<HotelFormField> = [
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
  {
    key: 'season_id',
    label: 'Season',
    type: 'select',
    required: true,
    hint: 'One price per room type and season',
    optionsLoader: () => client.listSeasonOptions(),
  },
  {
    key: 'amount_cents',
    label: 'Price per night',
    type: 'money',
    required: true,
    min: 0,
    hint: 'Amount in EUR, stored as integer cents',
  },
]
</script>

<template>
  <HotelCrudPage
    title="Season prices"
    subtitle="Room type price per season. Nightly posting uses the season of each posted night."
    endpoint="hotel/room-season-prices"
    view-path="/hotel/season-prices"
    :columns="columns"
    :fields="fields"
    add-label="New price"
  />
</template>
