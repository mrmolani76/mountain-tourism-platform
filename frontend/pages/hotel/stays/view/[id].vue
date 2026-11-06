<script setup lang="ts">
definePageMeta({ navActiveLink: 'hotel-stays' })
useHead({ title: 'Stay' })

const fields = [
  { key: 'id', title: 'Stay ID' },
  { key: 'reservation_id', title: 'Reservation' },
  { key: 'status', title: 'Status', kind: 'status' as const },
  { key: 'checked_in_at', title: 'Checked in', kind: 'datetime' as const },
  { key: 'checked_out_at', title: 'Checked out', kind: 'datetime' as const },
  { key: 'room_id', title: 'Room' },
  { key: 'hut_bed_id', title: 'Bed' },
]

const buildExtraTabs = () => [
  {
    icon: 'tabler-receipt',
    tab: 'folio',
    title: 'Folio',
    pinnedTab: true,
    component: markRaw(resolveComponent('StayViewFolio') as object),
  },
]
</script>

<template>
  <HotelViewPage
    endpoint="hotel/stays"
    list-path="/hotel/stays"
    entity-label="Stay"
    icon="tabler-key"
    :fields="fields"
    :title-keys="['id']"
    :can-edit="false"
    :build-extra-tabs="buildExtraTabs"
  />
</template>
