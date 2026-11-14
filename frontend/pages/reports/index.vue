<script setup lang="ts">
definePageMeta({ navActiveLink: 'reports' })
useHead({ title: 'Reports' })

const sections = [
  {
    title: 'Today',
    description: 'Live occupancy, arrivals, revenue and ops widgets for the complex.',
    items: [
      { title: 'Today dashboard', to: '/reports/today', icon: 'tabler-dashboard', caption: 'Owner morning view' },
    ],
  },
  {
    title: 'Revenue',
    description: 'Date-range totals by outlet, charge source and payment method (non-voided).',
    items: [
      { title: 'Revenue reports', to: '/reports/revenue', icon: 'tabler-chart-bar', caption: 'Outlet / service / payment + CSV' },
    ],
  },
  {
    title: 'Weather',
    description: 'Staff cancel-for-weather history for the selected range.',
    items: [
      { title: 'Weather cancellations', to: '/reports/weather-cancellations', icon: 'tabler-cloud-off', caption: 'List and CSV export' },
    ],
  },
]
</script>

<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h5 mb-1">
        Reports
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Owner and manager dashboard plus date-range revenue and weather cancellation reports.
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
