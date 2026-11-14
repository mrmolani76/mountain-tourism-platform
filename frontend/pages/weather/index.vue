<script setup lang="ts">
definePageMeta({ navActiveLink: 'weather' })
useHead({ title: 'Weather' })

const sections = [
  {
    title: 'Operations',
    description: 'Fetch forecasts, raise and clear warnings, cancel for weather.',
    items: [
      { title: 'Points', to: '/weather/points', icon: 'tabler-map-pin', caption: 'Sites, fetch and evaluate' },
      { title: 'Warnings', to: '/weather/warnings', icon: 'tabler-alert-triangle', caption: 'Active warnings and cancel' },
    ],
  },
  {
    title: 'Rules',
    description: 'Thresholds that turn forecast values into warnings.',
    items: [
      { title: 'Limits', to: '/weather/limits', icon: 'tabler-ruler', caption: 'Variable, operator, severity' },
    ],
  },
  {
    title: 'Bulletin',
    description: 'Daily avalanche level stub for lobby and staff.',
    items: [
      { title: 'Avalanche', to: '/weather/avalanche', icon: 'tabler-mountain', caption: 'Level 1–5 by day' },
    ],
  },
]
</script>

<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h5 mb-1">
        Weather
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Points, limits, warnings and cancel-for-weather voids. Never auto-cancels.
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
