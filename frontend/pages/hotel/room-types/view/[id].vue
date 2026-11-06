<script setup lang="ts">
definePageMeta({ navActiveLink: 'hotel-room-types' })
useHead({ title: 'Room type' })

const fields = [
  { key: 'name', title: 'Name' },
  { key: 'description', title: 'Description' },
  { key: 'max_adults', title: 'Max adults' },
  { key: 'max_children', title: 'Max children' },
  { key: 'is_active', title: 'Status', kind: 'bool' as const },
]
</script>

<template>
  <HotelViewPage
    endpoint="hotel/room-types"
    list-path="/hotel/room-types"
    entity-label="Room type"
    icon="tabler-category"
    :fields="fields"
  />
</template>
