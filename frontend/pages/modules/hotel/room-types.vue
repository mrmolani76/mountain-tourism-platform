<script setup lang="ts">
import type { HotelColumn, HotelFormField } from '@/types/hotel'

useHead({ title: 'Room types' })

const columns: Array<HotelColumn> = [
  { key: 'name', title: 'Name' },
  { key: 'max_adults', title: 'Max adults', align: 'end' },
  { key: 'max_children', title: 'Max children', align: 'end' },
  { key: 'is_active', title: 'Status' },
]

const fields: Array<HotelFormField> = [
  { key: 'name', label: 'Name', required: true, col: 12 },
  { key: 'description', label: 'Description', type: 'textarea', col: 12 },
  { key: 'max_adults', label: 'Max adults', type: 'number', min: 1, max: 50 },
  { key: 'max_children', label: 'Max children', type: 'number', min: 0, max: 50 },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <HotelCrudPage
    title="Room types"
    subtitle="Capacity per room type. Hut beds use the dormitory room type price."
    endpoint="hotel/room-types"
    :columns="columns"
    :fields="fields"
    add-label="New room type"
  />
</template>
