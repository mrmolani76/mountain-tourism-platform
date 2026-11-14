<script setup lang="ts">
import type { ServiceRow } from '@/composables/api-clients/useServicesApiClient'

definePageMeta({ navActiveLink: 'services' })
useHead({ title: 'Service catalog' })

const client = useServicesApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<ServiceRow[]>([])
const dialogOpen = ref(false)
const form = ref({
  name: '',
  service_type: 'spa',
  duration_minutes: 60,
  default_capacity: 1,
  price: 0,
  price_mode: 'per_person',
  outdoor: false,
  is_active: true,
})

const priceModes = [
  { title: 'Per person', value: 'per_person' },
  { title: 'Per booking', value: 'per_booking' },
  { title: 'Included', value: 'included' },
]

async function load() {
  loading.value = true
  const response = await client.listCatalog()
  rows.value = response?.results ?? []
  loading.value = false
}

function openCreate() {
  form.value = {
    name: '',
    service_type: 'spa',
    duration_minutes: 60,
    default_capacity: 1,
    price: 0,
    price_mode: 'per_person',
    outdoor: false,
    is_active: true,
  }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  await client.createService({
    name: form.value.name,
    service_type: form.value.service_type,
    duration_minutes: form.value.duration_minutes,
    default_capacity: form.value.default_capacity,
    price_cents: majorToCents(form.value.price),
    price_mode: form.value.price_mode,
    outdoor: form.value.outdoor,
    is_active: form.value.is_active,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Service saved', 'Service is in the catalog.', 'OK')
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
          Catalog
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Bookable services with duration, capacity and price mode.
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
          Add service
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Duration</th>
            <th>Capacity</th>
            <th class="text-end">
              Price
            </th>
            <th>Mode</th>
            <th>Active</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="7"
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
            <td>{{ row.name }}</td>
            <td>{{ row.service_type || '—' }}</td>
            <td>{{ row.duration_minutes }} min</td>
            <td>{{ row.default_capacity }}</td>
            <td class="text-end">
              {{ formatCents(row.price_cents) }}
            </td>
            <td>{{ row.price_mode }}</td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="row.is_active ? 'success' : 'default'"
              >
                {{ row.is_active ? 'Yes' : 'No' }}
              </VChip>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td
              colspan="7"
              class="text-center text-medium-emphasis py-8"
            >
              No services yet.
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
        <VCardTitle>Add service</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField
            v-model="form.name"
            label="Name"
          />
          <AppTextField
            v-model="form.service_type"
            label="Type"
          />
          <AppTextField
            v-model.number="form.duration_minutes"
            type="number"
            min="1"
            label="Duration (minutes)"
          />
          <AppTextField
            v-model.number="form.default_capacity"
            type="number"
            min="1"
            label="Default capacity"
          />
          <AppTextField
            v-model.number="form.price"
            type="number"
            step="0.01"
            min="0"
            label="Price (EUR)"
          />
          <AppSelect
            v-model="form.price_mode"
            :items="priceModes"
            item-title="title"
            item-value="value"
            label="Price mode"
          />
          <VSwitch
            v-model="form.outdoor"
            label="Outdoor"
            color="primary"
            hide-details
          />
          <VSwitch
            v-model="form.is_active"
            label="Active"
            color="primary"
            hide-details
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
            :disabled="!form.name"
            @click="save"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
