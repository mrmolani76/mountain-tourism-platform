<script setup lang="ts">
import type { Driver, TransportRoute, Vehicle } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Pickup from reservation' })

const client = useTransportApiClient()
const saving = ref(false)
const vehicles = ref<Vehicle[]>([])
const drivers = ref<Driver[]>([])
const routes = ref<TransportRoute[]>([])
const form = ref({
  reservation_id: null as number | null,
  route_id: null as number | null,
  vehicle_id: null as number | null,
  driver_id: null as number | null,
  notes: '',
})

async function loadMeta() {
  const [v, d, r] = await Promise.all([
    client.listVehicles(),
    client.listDrivers(),
    client.listRoutes(),
  ])
  vehicles.value = v?.results ?? []
  drivers.value = d?.results ?? []
  routes.value = r?.results ?? []
}

async function submit() {
  if (!form.value.reservation_id)
    return
  saving.value = true
  const trip = await client.pickupFromReservation({
    reservation_id: form.value.reservation_id,
    route_id: form.value.route_id || undefined,
    vehicle_id: form.value.vehicle_id || undefined,
    driver_id: form.value.driver_id || undefined,
    notes: form.value.notes || undefined,
  })
  saving.value = false
  if (trip?.id) {
    showAlertDialog('Pickup trip created', `Trip #${trip.id} from reservation arrival.`, 'OK')
    await navigateTo(`/transport/trips/view/${trip.id}`)
  }
}

onMounted(loadMeta)
</script>

<template>
  <div>
    <div class="mb-4">
      <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
      <h2 class="text-h5 mb-1">Pickup from reservation</h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Train/plane/bus arrival on a reservation creates a pickup trip with the guest as passenger.
      </p>
    </div>

    <VCard max-width="560">
      <VCardText class="d-flex flex-column gap-3">
        <AppTextField v-model.number="form.reservation_id" type="number" label="Reservation ID" />
        <AppSelect v-model="form.route_id" :items="routes" item-title="name" item-value="id" label="Route (optional)" clearable />
        <AppSelect v-model="form.vehicle_id" :items="vehicles" item-title="name" item-value="id" label="Vehicle (optional)" clearable />
        <AppSelect v-model="form.driver_id" :items="drivers" item-title="name" item-value="id" label="Driver (optional)" clearable />
        <AppTextField v-model="form.notes" label="Notes" />
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="primary" :loading="saving" :disabled="!form.reservation_id" @click="submit">
          Create pickup trip
        </VBtn>
      </VCardActions>
    </VCard>
  </div>
</template>
