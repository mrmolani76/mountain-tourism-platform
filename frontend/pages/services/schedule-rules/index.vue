<script setup lang="ts">
import type { ServiceRow } from '@/composables/api-clients/useServicesApiClient'

definePageMeta({ navActiveLink: 'services' })
useHead({ title: 'Schedule rules' })

const client = useServicesApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<Array<Record<string, any>>>([])
const services = ref<ServiceRow[]>([])
const dialogOpen = ref(false)
const form = ref({
  service_id: null as number | null,
  weekday: 1,
  starts_at: '09:00',
  ends_at: '10:00',
  capacity: 1,
  valid_from: new Date().toISOString().slice(0, 10),
  valid_to: '',
  is_active: true,
})

const weekdays = [
  { title: 'Sunday', value: 0 },
  { title: 'Monday', value: 1 },
  { title: 'Tuesday', value: 2 },
  { title: 'Wednesday', value: 3 },
  { title: 'Thursday', value: 4 },
  { title: 'Friday', value: 5 },
  { title: 'Saturday', value: 6 },
]

async function load() {
  loading.value = true
  const [rules, catalog] = await Promise.all([
    client.listScheduleRules(),
    client.listCatalog(),
  ])
  rows.value = rules?.results ?? []
  services.value = catalog?.results ?? []
  loading.value = false
}

function serviceName(id: number) {
  return services.value.find(s => s.id === id)?.name || `Service ${id}`
}

function weekdayName(v: number) {
  return weekdays.find(w => w.value === v)?.title || String(v)
}

function openCreate() {
  form.value = {
    service_id: services.value[0]?.id ?? null,
    weekday: 1,
    starts_at: '09:00',
    ends_at: '10:00',
    capacity: 1,
    valid_from: new Date().toISOString().slice(0, 10),
    valid_to: '',
    is_active: true,
  }
  dialogOpen.value = true
}

async function save() {
  if (!form.value.service_id)
    return
  saving.value = true
  await client.createScheduleRule({
    service_id: form.value.service_id,
    weekday: form.value.weekday,
    starts_at: form.value.starts_at,
    ends_at: form.value.ends_at,
    capacity: form.value.capacity,
    valid_from: form.value.valid_from,
    valid_to: form.value.valid_to || null,
    is_active: form.value.is_active,
  })
  saving.value = false
  dialogOpen.value = false
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn
          variant="text"
          prepend-icon="tabler-arrow-left"
          :to="{ path: '/services' }"
          class="mb-1 px-0"
        >
          Services
        </VBtn>
        <h2 class="text-h5 mb-1">
          Schedule rules
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Weekday templates for generating or planning slots.
        </p>
      </div>
      <div class="d-flex gap-2">
        <VBtn
          variant="tonal"
          prepend-icon="tabler-refresh"
          :loading="loading"
          @click="load"
        >
          Refresh
        </VBtn>
        <VBtn
          color="primary"
          prepend-icon="tabler-plus"
          @click="openCreate"
        >
          Add rule
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Service</th>
            <th>Weekday</th>
            <th>Time</th>
            <th>Capacity</th>
            <th>Valid</th>
            <th>Active</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="6"
              class="text-center py-8"
            >
              <VProgressCircular
                indeterminate
                color="primary"
              />
            </td>
          </tr>
          <tr
            v-for="row in rows"
            v-else
            :key="row.id"
          >
            <td>{{ serviceName(row.service_id) }}</td>
            <td>{{ weekdayName(row.weekday) }}</td>
            <td>{{ row.starts_at }} – {{ row.ends_at }}</td>
            <td>{{ row.capacity }}</td>
            <td>{{ row.valid_from }} → {{ row.valid_to || 'open' }}</td>
            <td>{{ row.is_active ? 'Yes' : 'No' }}</td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td
              colspan="6"
              class="text-center text-medium-emphasis py-8"
            >
              No schedule rules yet.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog
      v-model="dialogOpen"
      max-width="520"
    >
      <VCard>
        <VCardTitle>Add schedule rule</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="form.service_id"
            :items="services"
            item-title="name"
            item-value="id"
            label="Service"
          />
          <AppSelect
            v-model="form.weekday"
            :items="weekdays"
            item-title="title"
            item-value="value"
            label="Weekday"
          />
          <AppTextField
            v-model="form.starts_at"
            type="time"
            label="Starts"
          />
          <AppTextField
            v-model="form.ends_at"
            type="time"
            label="Ends"
          />
          <AppTextField
            v-model.number="form.capacity"
            type="number"
            min="1"
            label="Capacity"
          />
          <AppTextField
            v-model="form.valid_from"
            type="date"
            label="Valid from"
          />
          <AppTextField
            v-model="form.valid_to"
            type="date"
            label="Valid to (optional)"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="dialogOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!form.service_id"
            @click="save"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
