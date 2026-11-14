<script setup lang="ts">
definePageMeta({ navActiveLink: 'pos' })
useHead({ title: 'POS' })

const sections = [
  {
    title: 'Cashier desk',
    description: 'Open orders, add lines, settle cash / card / room.',
    items: [
      { title: 'Orders', to: '/pos/orders', icon: 'tabler-receipt-2', caption: 'Open and settle tickets' },
      { title: 'Shifts', to: '/pos/shifts', icon: 'tabler-clock', caption: 'Open and close cashier shifts' },
    ],
  },
  {
    title: 'Catalog',
    description: 'Products and categories for outlets.',
    items: [
      { title: 'Products', to: '/pos/products', icon: 'tabler-shopping-bag', caption: 'Menu items and prices' },
    ],
  },
  {
    title: 'Kitchen',
    description: 'Packed lunch list for the day.',
    items: [
      { title: 'Packed lunches', to: '/pos/packed-lunches', icon: 'tabler-backpack', caption: 'Kitchen list by date' },
    ],
  },
]
</script>

<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h5 mb-1">
        Restaurants, huts and shop
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        POS catalog, cashier shifts and order settle. Money posts through Folio.
      </p>
    </div>

    <VRow>
      <VCol
        v-for="section in sections"
        :key="section.title"
        cols="12"
        md="4"
      >
        <div class="text-subtitle-1 mb-1">
          {{ section.title }}
        </div>
        <p class="text-body-2 text-medium-emphasis mb-3">
          {{ section.description }}
        </p>
        <VList
          class="border rounded"
          density="compact"
        >
          <VListItem
            v-for="item in section.items"
            :key="item.to"
            :to="item.to"
            :prepend-icon="item.icon"
            :title="item.title"
            :subtitle="item.caption"
          />
        </VList>
      </VCol>
    </VRow>
  </div>
</template>
