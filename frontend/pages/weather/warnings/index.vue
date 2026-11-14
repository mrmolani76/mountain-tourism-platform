<script setup lang="ts">
import type { WeatherWarning } from '@/composables/api-clients/useWeatherApiClient'
import { WEATHER_TARGET_TYPES } from '@/composables/api-clients/useWeatherApiClient'

definePageMeta({ navActiveLink: 'weather' })
useHead({ title: 'Weather warnings' })

const client = useWeatherApiClient()
const loading = ref(false)
const acting = ref(false)
const statusFilter = ref('active')
const rows = ref<WeatherWarning[]>([])
const cancelOpen = ref(false)
const cancelForm = ref({
  warning_id: null as number | null,
  target_type: 'lift',
  target_id: null as number | null,
  reason: 'weather',
  notes: '',
})

async function load() {
  loading.value = true
  rows.value = (await client.listWarnings({ status: statusFilter.value }))?.results ?? []
  loading.value = false
}

async function resolve(id: number) {
  acting.value = true
  await client.resolveWarning(id)
  acting.value = false
  showAlertDialog('Resolved', 'Warning marked resolved.', 'OK')
  await load()
}

function openCancel(row: WeatherWarning) {
  const first = row.targets?.[0]
  cancelForm.value = {
    warning_id: row.id,
    target_type: first?.target_type ?? 'lift',
    target_id: first?.target_id ?? null,
    reason: 'weather',
    notes: '',
  }
  cancelOpen.value = true
}

async function submitCancel() {
  if (!cancelForm.value.target_id)
    return
  acting.value = true
  const result = await client.cancelForWeather({
    warning_id: cancelForm.value.warning_id,
    target_type: cancelForm.value.target_type,
    target_id: cancelForm.value.target_id,
    reason: cancelForm.value.reason,
    notes: cancelForm.value.notes || undefined,
  })
  acting.value = false
  cancelOpen.value = false
  const voidCount = result?.voids?.length ?? 0
  const guestCount = result?.guests_to_notify?.length ?? 0
  showAlertDialog(
    'Cancelled for weather',
    `Voids: ${voidCount}. Guests to notify: ${guestCount}.`,
    'OK',
  )
  await load()
}

watch(statusFilter, load)
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
          Weather warnings
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Resolve clears the warning. Cancel-for-weather voids related folio charges (manager+).
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap align-center">
        <AppSelect
          v-model="statusFilter"
          :items="[
            { title: 'Active', value: 'active' },
            { title: 'Resolved', value: 'resolved' },
            { title: 'Expired', value: 'expired' },
          ]"
          item-title="title"
          item-value="value"
          label="Status"
          style="min-width: 140px"
          density="compact"
          hide-details
        />
        <VBtn
          variant="tonal"
          prepend-icon="tabler-refresh"
          :loading="loading"
          @click="load"
        >
          Refresh
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th>
            <th>Point</th>
            <th>Variable</th>
            <th>Forecast / Limit</th>
            <th>Status</th>
            <th>Targets</th>
            <th class="text-end">
              Actions
            </th>
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
            <td>{{ row.id }}</td>
            <td>#{{ row.weather_point_id }}</td>
            <td>{{ row.variable }}</td>
            <td>{{ row.forecast_value }} / {{ row.threshold }}</td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="row.status === 'active' ? 'warning' : undefined"
              >
                {{ row.status }}
              </VChip>
            </td>
            <td>
              <span v-if="!row.targets?.length">—</span>
              <span
                v-for="(t, i) in row.targets || []"
                v-else
                :key="t.id"
              >
                {{ t.target_type }}#{{ t.target_id }}{{ i < (row.targets!.length - 1) ? ', ' : '' }}
              </span>
            </td>
            <td class="text-end">
              <div class="d-flex justify-end gap-2 flex-wrap">
                <VBtn
                  v-if="row.status === 'active'"
                  size="small"
                  variant="tonal"
                  :loading="acting"
                  @click="resolve(row.id)"
                >
                  Resolve
                </VBtn>
                <VBtn
                  size="small"
                  variant="tonal"
                  color="error"
                  :loading="acting"
                  @click="openCancel(row)"
                >
                  Cancel for weather
                </VBtn>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td
              colspan="7"
              class="text-center text-medium-emphasis py-8"
            >
              No warnings for this status.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog
      v-model="cancelOpen"
      max-width="520"
    >
      <VCard>
        <VCardTitle>Cancel for weather</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <p class="text-body-2 text-medium-emphasis mb-0">
            Explicit cancel only. Related folio charges are voided with reason weather.
          </p>
          <AppSelect
            v-model="cancelForm.target_type"
            :items="[...WEATHER_TARGET_TYPES]"
            item-title="title"
            item-value="value"
            label="Target type"
          />
          <AppTextField
            v-model.number="cancelForm.target_id"
            type="number"
            label="Target ID"
          />
          <AppTextField
            v-model="cancelForm.reason"
            label="Reason"
          />
          <AppTextField
            v-model="cancelForm.notes"
            label="Notes (optional)"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="cancelOpen = false"
          >
            Back
          </VBtn>
          <VBtn
            color="error"
            :loading="acting"
            :disabled="!cancelForm.target_id || !cancelForm.reason"
            @click="submitCancel"
          >
            Confirm cancel
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
