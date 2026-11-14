<script setup lang="ts">
import type { WeatherPoint } from '@/composables/api-clients/useWeatherApiClient'

definePageMeta({ navActiveLink: 'weather' })
useHead({ title: 'Weather points' })

const client = useWeatherApiClient()
const loading = ref(false)
const saving = ref(false)
const actingId = ref<number | null>(null)
const rows = ref<WeatherPoint[]>([])
const dialogOpen = ref(false)
const form = ref({
  name: '',
  latitude: 46.8,
  longitude: 9.8,
  altitude_m: null as number | null,
  building_id: null as number | null,
  lift_id: null as number | null,
  is_active: true,
})

async function load() {
  loading.value = true
  rows.value = (await client.listPoints())?.results ?? []
  loading.value = false
}

function openCreate() {
  form.value = {
    name: '',
    latitude: 46.8,
    longitude: 9.8,
    altitude_m: null,
    building_id: null,
    lift_id: null,
    is_active: true,
  }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  await client.createPoint({
    name: form.value.name,
    latitude: form.value.latitude,
    longitude: form.value.longitude,
    altitude_m: form.value.altitude_m,
    building_id: form.value.building_id,
    lift_id: form.value.lift_id,
    is_active: form.value.is_active,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Weather point created.', 'OK')
  await load()
}

async function runFetch(id: number) {
  actingId.value = id
  const result = await client.fetchPoint(id)
  actingId.value = null
  const provider = result?.provider || 'provider'
  showAlertDialog(
    'Fetch complete',
    result?.kept_previous
      ? `Kept previous snapshot (${provider}).`
      : `New snapshot from ${provider}.`,
    'OK',
  )
}

async function runEvaluate(id: number) {
  actingId.value = id
  const result = await client.evaluatePoint(id)
  actingId.value = null
  showAlertDialog(
    'Evaluate complete',
    `Created ${result?.created ?? 0}, updated ${result?.updated ?? 0} warning(s).`,
    'OK',
  )
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
          :to="{ path: '/weather' }"
          class="mb-1 px-0"
        >
          Weather
        </VBtn>
        <h2 class="text-h5 mb-1">
          Weather points
        </h2>
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
          Add point
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Name</th>
            <th>Lat / Lon</th>
            <th>Altitude</th>
            <th>Active</th>
            <th class="text-end">
              Actions
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="5"
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
            <td>{{ row.latitude }}, {{ row.longitude }}</td>
            <td>{{ row.altitude_m ?? '—' }}</td>
            <td>{{ row.is_active ? 'Yes' : 'No' }}</td>
            <td class="text-end">
              <div class="d-flex justify-end gap-2 flex-wrap">
                <VBtn
                  size="small"
                  variant="tonal"
                  :loading="actingId === row.id"
                  @click="runFetch(row.id)"
                >
                  Fetch
                </VBtn>
                <VBtn
                  size="small"
                  variant="tonal"
                  color="warning"
                  :loading="actingId === row.id"
                  @click="runEvaluate(row.id)"
                >
                  Evaluate
                </VBtn>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td
              colspan="5"
              class="text-center text-medium-emphasis py-8"
            >
              No weather points yet.
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
        <VCardTitle>Add weather point</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField
            v-model="form.name"
            label="Name"
          />
          <AppTextField
            v-model.number="form.latitude"
            type="number"
            step="0.0001"
            label="Latitude"
          />
          <AppTextField
            v-model.number="form.longitude"
            type="number"
            step="0.0001"
            label="Longitude"
          />
          <AppTextField
            v-model.number="form.altitude_m"
            type="number"
            label="Altitude (m)"
          />
          <AppTextField
            v-model.number="form.building_id"
            type="number"
            label="Building ID (optional)"
          />
          <AppTextField
            v-model.number="form.lift_id"
            type="number"
            label="Lift ID (optional)"
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
