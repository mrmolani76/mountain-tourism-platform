<script setup lang="ts">
definePageMeta({ navActiveLink: 'hotel-board-plans' })
useHead({ title: 'Board plan' })

const fields = [
  { key: 'code', title: 'Code' },
  { key: 'name', title: 'Name' },
  { key: 'price_per_person_cents', title: 'Per person / night', kind: 'money' as const },
  { key: 'inclusions', title: 'Inclusions' },
  { key: 'is_active', title: 'Status', kind: 'bool' as const },
]
</script>

<template>
  <HotelViewPage
    endpoint="hotel/board-plans"
    list-path="/hotel/board-plans"
    entity-label="Board plan"
    icon="tabler-tools-kitchen-2"
    :fields="fields"
    :title-keys="['name', 'code', 'id']"
  />
</template>
