<script setup lang="ts">
import type { HotelColumn, HotelFormField } from '@/types/hotel'

useHead({ title: 'Buildings' })

const columns: Array<HotelColumn> = [
  { key: 'name', title: 'Name' },
  { key: 'building_type', title: 'Type' },
  { key: 'altitude_m', title: 'Altitude (m)', align: 'end' },
  { key: 'is_active', title: 'Status', kind: 'bool' },
]

const fields: Array<HotelFormField> = [
  { key: 'name', label: 'Name', required: true, col: 12 },
  {
    key: 'building_type',
    label: 'Building type',
    type: 'select',
    required: true,
    options: [
      { value: 'valley_station', title: 'Valley Station' },
      { value: 'main_hotel', title: 'Main Hotel' },
      { value: 'chalets', title: 'Chalets' },
      { value: 'mid_station', title: 'Mid Station' },
      { value: 'mountain_hut', title: 'Mountain Hut' },
      { value: 'summit_station', title: 'Summit Station' },
      { value: 'other', title: 'Other' },
    ],
  },
  { key: 'altitude_m', label: 'Altitude (m)', type: 'number', min: 0 },
  { key: 'latitude', label: 'Latitude', type: 'number', step: 0.000001 },
  { key: 'longitude', label: 'Longitude', type: 'number', step: 0.000001 },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <ResourceCrudPage
    title="Buildings"
    subtitle="Places in the complex. Also used as weather points."
    endpoint="buildings"
    :columns="columns"
    :fields="fields"
    :defaults="{ building_type: 'other', is_active: true }"
    add-label="New building"
  />
</template>
