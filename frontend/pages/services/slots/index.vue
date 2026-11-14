<script setup lang="ts">
import type { ServiceRow, SlotRow } from '@/composables/api-clients/useServicesApiClient'

definePageMeta({ navActiveLink: 'services' })
useHead({ title: 'Service slots' })

const client = useServicesApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<SlotRow[]>([])
const services = ref<ServiceRow[]>([])
const dialogOpen = ref(false)
const form = ref({
  service_id: null as number | null,
  starts_at: '',
  ends_at: '',
  capacity: 4,
})

async function load() {
  loading.value = true
  const [slotRes, svcRes] = await Promise.all([
    client.listSlots(),
    client.listCatalog(),
  ])
  rows.value = slotRes?.results ?? []
  services.value = svcRes?.results ?? []
  loading.value = false
}

function serviceName(id: number) {
  return services.value.find(s => s.id === id)?.name || String(id)
}

function openCreate() {
  const start = new Date()
  start.setMinutes(0, 0, 0)
  start.setHours(start.getHours() + 1)
  const end = new Date(start.getTime() + 60 * 60 * 1000)
  form.value = {
    service_id: services.value[0]?.id ?? null,
    starts_at: start.toISOString().slice(0, 16),
    ends_at: end.toISOString().slice(0, 16),
    capacity: services.value[0]?.default_capacity || 4,
  }
  dialogOpen.value = true
}

async function save() {
  if (!form.value.service_id)
    return
  saving.value = true
  await client.createSlot({
    service_id: form.value.service_id,
    starts_at: new Date(form.value.starts_at).toISOString(),
    ends_at: new Date(form.value.ends_at).toISOString(),
    capacity: form.value.capacity,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Slot created.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/services' }" class="mb-1 px-0">
          Services
        </VBtn>
        <h2 class="text-h5 mb-1">
          Slots
        </h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">
          Refresh
        </VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">
          Add slot
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Service</th>
            <th>Starts</th>
            <th>Ends</th>
            <th>Booked</th>
            <th>Left</th>
            <th>Status</th>
            <th class="text-end">
              Actions
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" />
            </td>
          </tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ serviceName(row.service_id) }}</td>
            <td>{{ row.starts_at }}</td>
            <td>{{ row.ends_at }}</td>
            <td>{{ row.booked_count }} / {{ row.capacity }}</td>
            <td>{{ row.remaining_capacity }}</td>
            <td>
              <VChip size="small" variant="tonal">
                {{ row.status }}
              </VChip>
            </td>
            <td class="text-end">
              <VBtn size="small" variant="tonal" color="primary" :to="`/services/slots/view/${row.id}`">
                Open
              </VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td colspan="7" class="text-center text-medium-emphasis py-8">
              No slots yet.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="520">
      <VCard>
        <VCardTitle>Add slot</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect v-model="form.service_id" :items="services" item-title="name" item-value="id" label="Service" />
          <AppTextField v-model="form.starts_at" type="datetime-local" label="Starts" />
          <AppTextField v-model="form.ends_at" type="datetime-local" label="Ends" />
          <AppTextField v-model.number="form.capacity" type="number" min="1" label="Capacity" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">
            Cancel
          </VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!form.service_id" @click="save">
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
