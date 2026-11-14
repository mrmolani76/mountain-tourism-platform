<script setup lang="ts">
import type { WeatherCancellationReport } from '@/composables/api-clients/useReportsApiClient'

definePageMeta({ navActiveLink: 'reports' })
useHead({ title: 'Weather cancellations' })

const client = useReportsApiClient()
const loading = ref(false)
const downloading = ref(false)

function defaultFrom(): string {
  const d = new Date()
  d.setDate(1)

  return d.toISOString().slice(0, 10)
}

const fromDate = ref(defaultFrom())
const toDate = ref(new Date().toISOString().slice(0, 10))
const report = ref<WeatherCancellationReport | null>(null)

const rangeParams = computed(() => ({
  from_date: fromDate.value,
  to_date: toDate.value,
}))

async function load() {
  if (!fromDate.value || !toDate.value)
    return
  loading.value = true
  report.value = await client.weatherCancellations(rangeParams.value)
  loading.value = false
}

async function downloadCsv() {
  downloading.value = true
  await client.downloadWeatherCancellationsCsv(rangeParams.value)
  downloading.value = false
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
          :to="{ path: '/reports' }"
          class="mb-1 px-0"
        >
          Reports
        </VBtn>
        <h2 class="text-h5 mb-1">
          Weather cancellations
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Explicit cancel-for-weather actions in the selected range.
        </p>
      </div>
    </div>

    <VCard class="mb-4">
      <VCardText>
        <VRow align="end">
          <VCol
            cols="12"
            sm="4"
            md="3"
          >
            <AppTextField
              v-model="fromDate"
              type="date"
              label="From"
              density="comfortable"
            />
          </VCol>
          <VCol
            cols="12"
            sm="4"
            md="3"
          >
            <AppTextField
              v-model="toDate"
              type="date"
              label="To"
              density="comfortable"
            />
          </VCol>
          <VCol
            cols="12"
            sm="4"
            md="6"
            class="d-flex flex-wrap gap-2"
          >
            <VBtn
              color="primary"
              :loading="loading"
              @click="load"
            >
              Run
            </VBtn>
            <VBtn
              variant="tonal"
              prepend-icon="tabler-download"
              :loading="downloading"
              @click="downloadCsv"
            >
              CSV
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard>
      <VCardTitle class="d-flex flex-wrap justify-space-between align-center gap-2">
        <span>
          Cancellations
          <span
            v-if="report"
            class="text-body-2 text-medium-emphasis ms-2"
          >
            {{ report.from_date }} → {{ report.to_date }} · {{ report.count }} rows
          </span>
        </span>
      </VCardTitle>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th>
            <th>Target</th>
            <th>Cancelled at</th>
            <th>Reason</th>
            <th>Notes</th>
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
            v-for="row in report?.rows ?? []"
            v-else
            :key="row.id"
          >
            <td>{{ row.id }}</td>
            <td>{{ row.target_type }} #{{ row.target_id }}</td>
            <td>{{ row.cancelled_at || '—' }}</td>
            <td>{{ row.reason || '—' }}</td>
            <td>{{ row.notes || '—' }}</td>
          </tr>
          <tr v-if="!loading && !(report?.rows?.length)">
            <td
              colspan="5"
              class="text-center text-medium-emphasis py-8"
            >
              No cancellations in this range.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
