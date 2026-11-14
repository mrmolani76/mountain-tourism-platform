<script setup lang="ts">
import type { WeatherLimit, WeatherPoint } from '@/composables/api-clients/useWeatherApiClient'
import {
  WEATHER_OPERATORS,
  WEATHER_SEVERITIES,
  WEATHER_VARIABLES,
} from '@/composables/api-clients/useWeatherApiClient'

definePageMeta({ navActiveLink: 'weather' })
useHead({ title: 'Weather limits' })

const client = useWeatherApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<WeatherLimit[]>([])
const points = ref<WeatherPoint[]>([])
const dialogOpen = ref(false)
const form = ref({
  weather_point_id: null as number | null,
  variable: 'wind_ms',
  operator: 'gte',
  threshold: 15,
  severity: 'warning',
  is_active: true,
})

const pointItems = computed(() =>
  points.value.map(p => ({ title: p.name, value: p.id })),
)

function pointName(id: number) {
  return points.value.find(p => p.id === id)?.name ?? `#${id}`
}

async function load() {
  loading.value = true
  const [limitRes, pointRes] = await Promise.all([
    client.listLimits(),
    client.listPoints(),
  ])
  rows.value = limitRes?.results ?? []
  points.value = pointRes?.results ?? []
  loading.value = false
}

function openCreate() {
  form.value = {
    weather_point_id: points.value[0]?.id ?? null,
    variable: 'wind_ms',
    operator: 'gte',
    threshold: 15,
    severity: 'warning',
    is_active: true,
  }
  dialogOpen.value = true
}

async function save() {
  if (!form.value.weather_point_id)
    return
  saving.value = true
  await client.createLimit({
    weather_point_id: form.value.weather_point_id,
    variable: form.value.variable,
    operator: form.value.operator,
    threshold: form.value.threshold,
    severity: form.value.severity,
    is_active: form.value.is_active,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Weather limit created.', 'OK')
  await load()
}

async function toggleActive(row: WeatherLimit) {
  await client.updateLimit(row.id, { is_active: !row.is_active })
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
          :to="{ path: '/weather' }"
          class="mb-1 px-0"
        >
          Weather
        </VBtn>
        <h2 class="text-h5 mb-1">
          Weather limits
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
          Add limit
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Point</th>
            <th>Variable</th>
            <th>Rule</th>
            <th>Severity</th>
            <th>Active</th>
            <th class="text-end">
              Actions
            </th>
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
            <td>{{ pointName(row.weather_point_id) }}</td>
            <td>{{ row.variable }}</td>
            <td>{{ row.operator }} {{ row.threshold }}</td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="row.severity === 'danger' ? 'error' : row.severity === 'warning' ? 'warning' : undefined"
              >
                {{ row.severity }}
              </VChip>
            </td>
            <td>{{ row.is_active ? 'Yes' : 'No' }}</td>
            <td class="text-end">
              <VBtn
                size="small"
                variant="tonal"
                @click="toggleActive(row)"
              >
                {{ row.is_active ? 'Deactivate' : 'Activate' }}
              </VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td
              colspan="6"
              class="text-center text-medium-emphasis py-8"
            >
              No limits yet.
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
        <VCardTitle>Add weather limit</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="form.weather_point_id"
            :items="pointItems"
            item-title="title"
            item-value="value"
            label="Weather point"
          />
          <AppSelect
            v-model="form.variable"
            :items="[...WEATHER_VARIABLES]"
            item-title="title"
            item-value="value"
            label="Variable"
          />
          <AppSelect
            v-model="form.operator"
            :items="[...WEATHER_OPERATORS]"
            item-title="title"
            item-value="value"
            label="Operator"
          />
          <AppTextField
            v-model.number="form.threshold"
            type="number"
            step="0.1"
            label="Threshold"
          />
          <AppSelect
            v-model="form.severity"
            :items="[...WEATHER_SEVERITIES]"
            item-title="title"
            item-value="value"
            label="Severity"
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
            :disabled="!form.weather_point_id"
            @click="save"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
