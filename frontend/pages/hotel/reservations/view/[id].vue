<script setup lang="ts">
definePageMeta({ navActiveLink: 'hotel-reservations' })
useHead({ title: 'Reservation' })

const fields = [
  { key: 'reference', title: 'Reference' },
  { key: 'guest_name', title: 'Guest' },
  { key: 'guest_email', title: 'Email' },
  { key: 'guest_phone', title: 'Phone' },
  { key: 'check_in_on', title: 'Check in', kind: 'date' as const },
  { key: 'check_out_on', title: 'Check out', kind: 'date' as const },
  { key: 'adults', title: 'Adults' },
  { key: 'children', title: 'Children' },
  { key: 'status', title: 'Status', kind: 'status' as const },
  { key: 'notes', title: 'Notes' },
]

const buildExtraTabs = () => [
  {
    icon: 'tabler-bed',
    tab: 'allocations',
    title: 'Allocations',
    pinnedTab: true,
    component: markRaw(resolveComponent('ReservationViewAllocations') as object),
  },
]
</script>

<template>
  <HotelViewPage
    endpoint="hotel/reservations"
    list-path="/hotel/reservations"
    entity-label="Reservation"
    icon="tabler-calendar-plus"
    :fields="fields"
    :title-keys="['reference', 'guest_name', 'id']"
    :can-edit="false"
    :build-extra-tabs="buildExtraTabs"
  />
</template>
