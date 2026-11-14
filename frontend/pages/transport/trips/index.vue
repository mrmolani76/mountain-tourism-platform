<script setup lang="ts">
import type { Driver, Trip, Vehicle } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Trips' })

const client = useTransportApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<Trip[]>([])
const vehicles = ref<Vehicle[]>([])
const drivers = ref<Driver[]>([])
const dialogOpen = ref(false)
const form = ref({
  trip_type: 'shuttle',
  vehicle_id: null as number | null,
  driver_id: null as number | null,
  starts_at: '',
  ends_at: '',
  seats: 8,
  notes: '',
})

async function load() {
  loading.value = true
  const [tripRes, vehRes, drvRes] = await Promise.all([
    client.listTrips(),
    client.listVehicles(),
    client.listDrivers(),
  ])
  rows.value = tripRes?.results ?? []
  vehicles.value = vehRes?.results ?? []
  drivers.value = drvRes?.results ?? []
  loading.value = false
}

function openCreate() {
  const start = new Date()
  start.setMinutes(0, 0, 0)
  start.setHours(start.getHours() + 1)
  const end = new Date(start.getTime() + 60 * 60 * 1000)
  form.value = {
    trip_type: 'shuttle',
    vehicle_id: vehicles.value[0]?.id ?? null,
    driver_id: drivers.value[0]?.id ?? null,
    starts_at: start.toISOString().slice(0, 16),
    ends_at: end.toISOString().slice(0, 16),
    seats: vehicles.value[0]?.seats || 8,
    notes: '',
  }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  const trip = await client.createTrip({
    trip_type: form.value.trip_type,
    vehicle_id: form.value.vehicle_id || undefined,
    driver_id: form.value.driver_id || undefined,
    starts_at: new Date(form.value.starts_at).toISOString(),
    ends_at: new Date(form.value.ends_at).toISOString(),
    seats: form.value.seats,
    notes: form.value.notes || undefined,
  })
  saving.value = false
  dialogOpen.value = false
  await navigateTo(`/transport/trips/view/${trip.id}`)
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
        <h2 class="text-h5 mb-1">Trips</h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" :to="{ path: '/transport/pickup' }">Pickup from reservation</VBtn>
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">Refresh</VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">New trip</VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th><th>Type</th><th>Starts</th><th>Seats left</th><th>Status</th><th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center py-8"><VProgressCircular indeterminate color="primary" /></td></tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ row.id }}</td>
            <td>{{ row.trip_type }}</td>
            <td>{{ row.starts_at }}</td>
            <td>{{ row.remaining_seats }} / {{ row.seats }}</td>
            <td><VChip size="small" variant="tonal">{{ row.status }}</VChip></td>
            <td class="text-end">
              <VBtn size="small" variant="tonal" color="primary" :to="`/transport/trips/view/${row.id}`">Open</VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length"><td colspan="6" class="text-center text-medium-emphasis py-8">No trips yet.</td></tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="520">
      <VCard>
        <VCardTitle>New trip</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="form.trip_type"
            :items="[
              { title: 'Shuttle', value: 'shuttle' },
              { title: 'Taxi', value: 'taxi' },
              { title: 'Pickup', value: 'pickup' },
              { title: 'Dropoff', value: 'dropoff' },
            ]"
            item-title="title" item-value="value" label="Type"
          />
          <AppSelect v-model="form.vehicle_id" :items="vehicles" item-title="name" item-value="id" label="Vehicle" clearable />
          <AppSelect v-model="form.driver_id" :items="drivers" item-title="name" item-value="id" label="Driver" clearable />
          <AppTextField v-model="form.starts_at" type="datetime-local" label="Starts" />
          <AppTextField v-model="form.ends_at" type="datetime-local" label="Ends" />
          <AppTextField v-model.number="form.seats" type="number" min="1" label="Seats" />
          <AppTextField v-model="form.notes" label="Notes" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">Cancel</VBtn>
          <VBtn color="primary" :loading="saving" @click="save">Create</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
