<script setup lang="ts">
import type { Driver, Vehicle } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Pickup from reservation' })

const client = useTransportApiClient()
const saving = ref(false)
const vehicles = ref<Vehicle[]>([])
const drivers = ref<Driver[]>([])
const form = ref({
  reservation_id: null as number | null,
  vehicle_id: null as number | null,
  driver_id: null as number | null,
  notes: '',
})

async function loadOptions() {
  const [veh, drv] = await Promise.all([client.listVehicles(), client.listDrivers()])
  vehicles.value = veh?.results ?? []
  drivers.value = drv?.results ?? []
}

async function submit() {
  if (!form.value.reservation_id)
    return
  saving.value = true
  const trip = await client.pickupFromReservation({
    reservation_id: form.value.reservation_id,
    vehicle_id: form.value.vehicle_id || undefined,
    driver_id: form.value.driver_id || undefined,
    notes: form.value.notes || undefined,
  })
  saving.value = false
  showAlertDialog('Pickup created', `Trip #${trip.id} scheduled from reservation arrival.`, 'OK')
  await navigateTo(`/transport/trips/view/${trip.id}`)
}

onMounted(loadOptions)
</script>

<template>
  <div>
    <div class="mb-4">
      <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
      <h2 class="text-h5 mb-1">Pickup from reservation</h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Uses reservation arrival mode/time/station. Car arrivals are rejected.
      </p>
    </div>

    <VCard max-width="520">
      <VCardText class="d-flex flex-column gap-3">
        <AppTextField v-model.number="form.reservation_id" type="number" min="1" label="Reservation ID" />
        <AppSelect v-model="form.vehicle_id" :items="vehicles" item-title="name" item-value="id" label="Vehicle (optional)" clearable />
        <AppSelect v-model="form.driver_id" :items="drivers" item-title="name" item-value="id" label="Driver (optional)" clearable />
        <AppTextField v-model="form.notes" label="Notes" />
        <VBtn color="primary" :loading="saving" :disabled="!form.reservation_id" @click="submit">
          Create pickup trip
        </VBtn>
      </VCardText>
    </VCard>
  </div>
</template>
