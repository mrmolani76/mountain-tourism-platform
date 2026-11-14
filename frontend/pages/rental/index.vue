<script setup lang="ts">
definePageMeta({ navActiveLink: 'rental' })
useHead({ title: 'Rental' })

const sections = [
  {
    title: 'Desk',
    description: 'Rent out gear and process returns.',
    items: [
      { title: 'Rent out', to: '/rental/rent-out', icon: 'tabler-skiing', caption: 'Create rental contract' },
      { title: 'Currently out', to: '/rental/out', icon: 'tabler-list-check', caption: 'Open lines and returns' },
      { title: 'All rentals', to: '/rental/contracts', icon: 'tabler-file-text', caption: 'Contract history' },
    ],
  },
  {
    title: 'Catalog',
    description: 'Equipment types, physical items and season rates.',
    items: [
      { title: 'Equipment types', to: '/rental/types', icon: 'tabler-category', caption: 'Skis, boots, bikes…' },
      { title: 'Items', to: '/rental/items', icon: 'tabler-barcode', caption: 'Physical stock codes' },
      { title: 'Season prices', to: '/rental/prices', icon: 'tabler-currency-euro', caption: 'Day rates by season' },
    ],
  },
]
</script>

<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h5 mb-1">
        Equipment rental
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Rent out, return, late and damage fees post to Folio.
      </p>
    </div>

    <VRow>
      <VCol
        v-for="section in sections"
        :key="section.title"
        cols="12"
        md="6"
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
