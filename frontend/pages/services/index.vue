<script setup lang="ts">
definePageMeta({ navActiveLink: 'services' })
useHead({ title: 'Services' })

const sections = [
  {
    title: 'Desk',
    description: 'Book slots and run guided outings.',
    items: [
      { title: 'Slots', to: '/services/slots', icon: 'tabler-calendar-time', caption: 'Capacity and remaining seats' },
      { title: 'Bookings', to: '/services/bookings', icon: 'tabler-ticket', caption: 'Book and cancel' },
    ],
  },
  {
    title: 'Setup',
    description: 'Catalog and weekly schedule rules.',
    items: [
      { title: 'Catalog', to: '/services/catalog', icon: 'tabler-list-details', caption: 'Spa, sports, activities' },
      { title: 'Schedule rules', to: '/services/schedule-rules', icon: 'tabler-calendar-repeat', caption: 'Weekday templates' },
    ],
  },
]
</script>

<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h5 mb-1">
        Hotel services and activities
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Slots, bookings and guided outing headcount. Charges post to Folio.
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
