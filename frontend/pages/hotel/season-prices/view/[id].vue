<script setup lang="ts">
definePageMeta({ navActiveLink: 'hotel-season-prices' })
useHead({ title: 'Season price' })

const client = useHotelApiClient()

const fields = [
  { key: 'room_type_id', title: 'Room type' },
  { key: 'season_id', title: 'Season' },
  { key: 'amount_cents', title: 'Amount', kind: 'money' as const },
]
</script>

<template>
  <HotelViewPage
    endpoint="hotel/room-season-prices"
    list-path="/hotel/season-prices"
    entity-label="Season price"
    icon="tabler-calendar-dollar"
    :fields="fields"
    :title-keys="['id']"
    :options-loaders="{
      room_type_id: async () => {
        const { data } = await client.listRoomTypes({ page: 1, page_size: 100 })
        return data.map(row => ({ value: row.id, title: row.name }))
      },
      season_id: () => client.listSeasonOptions(),
    }"
  />
</template>
