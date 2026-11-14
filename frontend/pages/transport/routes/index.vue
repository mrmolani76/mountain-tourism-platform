<script setup lang="ts">
import type { TransportRoute } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Routes' })

const client = useTransportApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<TransportRoute[]>([])
const dialogOpen = ref(false)
const form = ref({ name: '', route_type: 'shuttle', is_active: true, stop_name: '' })

async function load() {
  loading.value = true
  rows.value = (await client.listRoutes())?.results ?? []
  loading.value = false
}

function openCreate() {
  form.value = { name: '', route_type: 'shuttle', is_active: true, stop_name: '' }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  const stops = form.value.stop_name
    ? [{ sequence: 1, name: form.value.stop_name }]
    : []
  await client.createRoute({
    name: form.value.name,
    route_type: form.value.route_type,
    is_active: form.value.is_active,
    stops,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Route created.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
        <h2 class="text-h5 mb-1">Routes</h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">Refresh</VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">Add route</VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead><tr><th>Name</th><th>Type</th><th>Stops</th><th>Active</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="4" class="text-center py-8"><VProgressCircular indeterminate color="primary" /></td></tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ row.name }}</td><td>{{ row.route_type }}</td>
            <td>{{ row.stops?.length ?? 0 }}</td><td>{{ row.is_active ? 'Yes' : 'No' }}</td>
          </tr>
          <tr v-if="!loading && !rows.length"><td colspan="4" class="text-center text-medium-emphasis py-8">No routes yet.</td></tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="480">
      <VCard>
        <VCardTitle>Add route</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField v-model="form.name" label="Name" />
          <AppSelect
            v-model="form.route_type"
            :items="[
              { title: 'Shuttle', value: 'shuttle' },
              { title: 'Taxi', value: 'taxi' },
              { title: 'Pickup', value: 'pickup' },
              { title: 'Dropoff', value: 'dropoff' },
            ]"
            item-title="title" item-value="value" label="Type"
          />
          <AppTextField v-model="form.stop_name" label="First stop name (optional)" />
          <VSwitch v-model="form.is_active" label="Active" color="primary" hide-details />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">Cancel</VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!form.name" @click="save">Save</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
