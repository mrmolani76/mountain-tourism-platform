<script setup lang="ts">
import type { Vehicle } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Vehicles' })

const client = useTransportApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<Vehicle[]>([])
const dialogOpen = ref(false)
const form = ref({ name: '', plate: '', vehicle_type: 'van', seats: 8, is_active: true })

async function load() {
  loading.value = true
  rows.value = (await client.listVehicles())?.results ?? []
  loading.value = false
}

function openCreate() {
  form.value = { name: '', plate: '', vehicle_type: 'van', seats: 8, is_active: true }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  await client.createVehicle(form.value)
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Vehicle created.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
        <h2 class="text-h5 mb-1">Vehicles</h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">Refresh</VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">Add vehicle</VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr><th>Name</th><th>Plate</th><th>Type</th><th>Seats</th><th>Active</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="text-center py-8"><VProgressCircular indeterminate color="primary" /></td></tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ row.name }}</td><td>{{ row.plate }}</td><td>{{ row.vehicle_type }}</td><td>{{ row.seats }}</td>
            <td>{{ row.is_active ? 'Yes' : 'No' }}</td>
          </tr>
          <tr v-if="!loading && !rows.length"><td colspan="5" class="text-center text-medium-emphasis py-8">No vehicles yet.</td></tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="480">
      <VCard>
        <VCardTitle>Add vehicle</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField v-model="form.name" label="Name" />
          <AppTextField v-model="form.plate" label="Plate" />
          <AppSelect
            v-model="form.vehicle_type"
            :items="[
              { title: 'Minibus', value: 'minibus' },
              { title: 'Van', value: 'van' },
              { title: 'Car', value: 'car' },
              { title: 'Snowmobile', value: 'snowmobile' },
              { title: 'Other', value: 'other' },
            ]"
            item-title="title" item-value="value" label="Type"
          />
          <AppTextField v-model.number="form.seats" type="number" min="1" label="Seats" />
          <VSwitch v-model="form.is_active" label="Active" color="primary" hide-details />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">Cancel</VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!form.name || !form.plate" @click="save">Save</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
