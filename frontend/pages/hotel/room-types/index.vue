<script setup lang="ts">
useHead({ title: 'Room types' })

const fields: Array<HotelFormField> = [
  { key: 'name', label: 'Name', required: true, col: 12 },
  { key: 'description', label: 'Description', type: 'textarea', col: 12 },
  { key: 'max_adults', label: 'Max adults', type: 'number', min: 1, max: 50 },
  { key: 'max_children', label: 'Max children', type: 'number', min: 0, max: 50 },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <HotelListingPage
    resource="room-types"
    title="Room types"
    subtitle="Capacity per room type. Hut beds use the dormitory room type price."
    endpoint="hotel/room-types"
    view-path="/hotel/room-types"
    :fields="fields"
    add-label="New room type"
  />
</template>
