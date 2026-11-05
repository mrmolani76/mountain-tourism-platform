<script setup lang="ts">
const client = useHotelApiClient()

const running = ref(false)
const lastRun = ref<Record<string, any> | null>(null)

const sections = [
  {
    title: 'Inventory',
    description: 'Room types, rooms, chalets and hut beds.',
    items: [
      { title: 'Room types', to: '/hotel/room-types', icon: 'tabler-category', caption: 'Capacity and description per type' },
      { title: 'Rooms & chalets', to: '/hotel/rooms', icon: 'tabler-door', caption: 'Rooms per building, chalets included' },
      { title: 'Hut dormitories', to: '/hotel/hut-dormitories', icon: 'tabler-home', caption: 'Dormitory blocks and their room type' },
      { title: 'Hut beds', to: '/hotel/hut-beds', icon: 'tabler-bed', caption: 'Individually bookable beds' },
    ],
  },
  {
    title: 'Pricing',
    description: 'Season prices and board plans.',
    items: [
      { title: 'Season prices', to: '/hotel/season-prices', icon: 'tabler-calendar-dollar', caption: 'Room type price per season' },
      { title: 'Board plans', to: '/hotel/board-plans', icon: 'tabler-tools-kitchen-2', caption: 'Meal plans and per person price' },
    ],
  },
  {
    title: 'Front desk',
    description: 'Booking, stays and nightly charges.',
    items: [
      { title: 'Reservations', to: '/hotel/reservations', icon: 'tabler-calendar-plus', caption: 'Book, edit, cancel and check in' },
      { title: 'Stays', to: '/hotel/stays', icon: 'tabler-key', caption: 'In-house guests, folio and checkout' },
      { title: 'Availability', to: '/hotel/availability', icon: 'tabler-calendar-search', caption: 'Free rooms and beds per date range' },
    ],
  },
]

const runNightlyPosting = async () => {
  running.value = true
  lastRun.value = await client.runNightlyPosting()
  running.value = false
}
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-6">
      <div>
        <h2 class="text-h5 mb-1">
          Hotel, chalets and huts
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Inventory, seasonal pricing, reservations and stays for the complex.
        </p>
      </div>
      <VBtn
        color="primary"
        variant="tonal"
        prepend-icon="tabler-clock-dollar"
        :loading="running"
        @click="runNightlyPosting"
      >
        Run nightly posting
      </VBtn>
    </div>

    <VAlert
      v-if="lastRun"
      type="success"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Nightly posting finished. {{ lastRun.posted ?? lastRun.posted_count ?? '' }} night(s) posted.
    </VAlert>

    <VRow>
      <VCol
        v-for="section in sections"
        :key="section.title"
        cols="12"
        md="4"
      >
        <VCard class="h-100">
          <VCardItem>
            <VCardTitle>{{ section.title }}</VCardTitle>
            <VCardSubtitle>{{ section.description }}</VCardSubtitle>
          </VCardItem>
          <VCardText class="pt-0">
            <VList density="compact" nav>
              <VListItem
                v-for="item in section.items"
                :key="item.to"
                :to="item.to"
                :prepend-icon="item.icon"
                :title="item.title"
                :subtitle="item.caption"
              />
            </VList>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
