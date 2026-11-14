<script setup lang="ts">
import type { Trip } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })

const route = useRoute()
const tripId = computed(() => Number(route.params.id))
useHead({ title: computed(() => `Trip ${tripId.value}`) })

const client = useTransportApiClient()
const loading = ref(true)
const saving = ref(false)
const trip = ref<Trip | null>(null)
const manifest = ref<Record<string, any> | null>(null)
const addOpen = ref(false)
const form = ref({ guest_name: '', seats: 1, stay_id: null as number | null })

async function load() {
  loading.value = true
  const [t, list] = await Promise.all([
    client.showTrip(tripId.value),
    client.passengerList(tripId.value),
  ])
  trip.value = t
  manifest.value = list
  loading.value = false
}

function openAdd() {
  form.value = { guest_name: '', seats: 1, stay_id: null }
  addOpen.value = true
}

async function submitAdd() {
  saving.value = true
  await client.addPassenger(tripId.value, {
    guest_name: form.value.guest_name,
    seats: form.value.seats,
    stay_id: form.value.stay_id || undefined,
  })
  saving.value = false
  addOpen.value = false
  await load()
}

async function cancelPassenger(id: number) {
  saving.value = true
  await client.cancelPassenger(id)
  saving.value = false
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport/trips' }" class="mb-1 px-0">Trips</VBtn>
        <h2 class="text-h5 mb-1">Trip #{{ tripId }}</h2>
        <p v-if="trip" class="text-body-2 text-medium-emphasis mb-0">
          {{ trip.trip_type }} · {{ trip.status }} · seats left {{ trip.remaining_seats }}/{{ trip.seats }}
        </p>
      </div>
      <VBtn v-if="trip && ['scheduled', 'running'].includes(trip.status)" color="primary" @click="openAdd">
        Add passenger
      </VBtn>
    </div>

    <div v-if="loading" class="text-center py-12">
      <VProgressCircular indeterminate color="primary" />
    </div>

    <template v-else>
      <VCard class="mb-4">
        <VCardTitle class="text-subtitle-1">Passenger list (print)</VCardTitle>
        <VCardText class="text-body-2">
          Driver: {{ manifest?.driver?.name || '—' }} · Vehicle: {{ manifest?.vehicle?.name || '—' }} ·
          Count: {{ manifest?.passenger_count ?? 0 }}
        </VCardText>
        <VTable density="compact">
          <thead>
            <tr><th>Guest</th><th>Seats</th><th>Status</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody>
            <tr v-for="p in (manifest?.passengers || [])" :key="p.id">
              <td>{{ p.guest_name }}</td>
              <td>{{ p.seats }}</td>
              <td>{{ p.status }}</td>
              <td class="text-end">
                <VBtn
                  v-if="p.status === 'booked'"
                  size="small"
                  variant="tonal"
                  color="warning"
                  :loading="saving"
                  @click="cancelPassenger(p.id)"
                >
                  Cancel
                </VBtn>
              </td>
            </tr>
            <tr v-if="!(manifest?.passengers || []).length">
              <td colspan="4" class="text-center text-medium-emphasis py-6">No passengers yet.</td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </template>

    <VDialog v-model="addOpen" max-width="480">
      <VCard>
        <VCardTitle>Add passenger</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField v-model="form.guest_name" label="Guest name" />
          <AppTextField v-model.number="form.seats" type="number" min="1" label="Seats" />
          <AppTextField v-model.number="form.stay_id" type="number" min="1" label="Stay ID (optional)" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="addOpen = false">Cancel</VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!form.guest_name" @click="submitAdd">Add</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
