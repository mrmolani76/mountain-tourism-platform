<script setup lang="ts">
import type { HotelColumn, HotelFormField } from '@/types/hotel'

useHead({ title: 'Board plans' })

const columns: Array<HotelColumn> = [
  { key: 'code', title: 'Code' },
  { key: 'name', title: 'Name' },
  { key: 'price_per_person_cents', title: 'Per person / night', kind: 'money', align: 'end' },
  { key: 'inclusions', title: 'Inclusions' },
  { key: 'is_active', title: 'Status' },
]

const fields: Array<HotelFormField> = [
  {
    key: 'code',
    label: 'Code',
    type: 'select',
    required: true,
    options: [
      { value: 'room_only', title: 'Room only' },
      { value: 'bed_breakfast', title: 'Bed and breakfast' },
      { value: 'half_board', title: 'Half board' },
      { value: 'full_board', title: 'Full board' },
    ],
  },
  { key: 'name', label: 'Name', required: true },
  {
    key: 'price_per_person_cents',
    label: 'Price per person / night',
    type: 'money',
    required: true,
    min: 0,
  },
  {
    key: 'inclusions',
    label: 'Meal inclusions',
    type: 'multiselect',
    col: 12,
    options: [
      { value: 'breakfast', title: 'Breakfast' },
      { value: 'lunch', title: 'Lunch' },
      { value: 'dinner', title: 'Dinner' },
    ],
  },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <HotelCrudPage
    title="Board plans"
    subtitle="Board price is charged per person per night (adults plus children)."
    endpoint="hotel/board-plans"
    :columns="columns"
    :fields="fields"
    add-label="New board plan"
  />
</template>
