<script setup lang="ts">
definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Transport' })

const sections = [
  {
    title: 'Desk',
    description: 'Trips, pickups and passenger lists for drivers.',
    items: [
      { title: 'Trips', to: '/transport/trips', icon: 'tabler-bus', caption: 'Schedule and book seats' },
      { title: 'Pickup from reservation', to: '/transport/pickup', icon: 'tabler-train', caption: 'External arrival → pickup trip' },
    ],
  },
  {
    title: 'Fleet',
    description: 'Vehicles, drivers and map-drawn routes.',
    items: [
      { title: 'Vehicles', to: '/transport/vehicles', icon: 'tabler-car', caption: 'Seats and plates' },
      { title: 'Drivers', to: '/transport/drivers', icon: 'tabler-user', caption: 'No login in MVP' },
      { title: 'Routes', to: '/transport/routes', icon: 'tabler-route', caption: 'Stops and path' },
    ],
  },
  {
    title: 'Lifts',
    description: 'Lift status and pass sales.',
    items: [
      { title: 'Lifts', to: '/transport/lifts', icon: 'tabler-building-bridge', caption: 'Open / closed / hold' },
      { title: 'Lobby status', to: '/transport/lobby', icon: 'tabler-device-tv', caption: 'Public lift board' },
      { title: 'Lift passes', to: '/transport/passes', icon: 'tabler-ticket', caption: 'Products and sell' },
    ],
  },
]
</script>

<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h5 mb-1">
        Transport and lifts
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Own fleet trips, reservation pickups, lift status and pass sales via Folio.
      </p>
    </div>

    <VRow>
      <VCol v-for="section in sections" :key="section.title" cols="12" md="4">
        <div class="text-subtitle-1 mb-1">
          {{ section.title }}
        </div>
        <p class="text-body-2 text-medium-emphasis mb-3">
          {{ section.description }}
        </p>
        <VList class="border rounded" density="compact">
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
