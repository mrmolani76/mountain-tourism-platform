<script setup lang="ts">
import type { TodayDashboard } from '@/composables/api-clients/useReportsApiClient'

definePageMeta({ navActiveLink: 'reports' })
useHead({ title: 'Today dashboard' })

const client = useReportsApiClient()
const loading = ref(false)
const data = ref<TodayDashboard | null>(null)

async function load() {
  loading.value = true
  data.value = await client.today()
  loading.value = false
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
          Today
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ data?.date ?? '…' }} · {{ data?.currency ?? 'EUR' }}
        </p>
      </div>
      <VBtn
        variant="tonal"
        prepend-icon="tabler-refresh"
        :loading="loading"
        @click="load"
      >
        Refresh
      </VBtn>
    </div>

    <div
      v-if="loading && !data"
      class="text-center py-12"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
    </div>

    <template v-else-if="data">
      <VRow>
        <VCol
          cols="12"
          md="4"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Open folios
            </VCardTitle>
            <VCardText>
              <div class="text-h4">
                {{ data.open_folios.count }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                Balance
                {{ data.open_folios.balance_cents == null ? '—' : formatCents(data.open_folios.balance_cents) }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Revenue today
            </VCardTitle>
            <VCardText>
              <div class="text-h4">
                {{ formatCents(data.revenue_by_outlet_today.total_cents) }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                Non-voided charges
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Rentals out
            </VCardTitle>
            <VCardText>
              <div class="text-h4">
                {{ data.rentals_out.count }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                Open or late contracts
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mt-2">
        <VCol
          cols="12"
          md="6"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Occupancy by building
            </VCardTitle>
            <VTable density="compact">
              <thead>
                <tr>
                  <th>Building</th>
                  <th class="text-end">
                    Rooms
                  </th>
                  <th class="text-end">
                    Occupied
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in data.occupancy_by_building.results"
                  :key="row.building_id"
                >
                  <td>{{ row.building_name }}</td>
                  <td class="text-end">
                    {{ row.rooms_total }}
                  </td>
                  <td class="text-end">
                    {{ row.occupied }}
                  </td>
                </tr>
                <tr v-if="!data.occupancy_by_building.results.length">
                  <td
                    colspan="3"
                    class="text-center text-medium-emphasis py-6"
                  >
                    No buildings.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Revenue by outlet
            </VCardTitle>
            <VTable density="compact">
              <thead>
                <tr>
                  <th>Outlet</th>
                  <th class="text-end">
                    Amount
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(row, idx) in data.revenue_by_outlet_today.results"
                  :key="`${row.outlet_id}-${idx}`"
                >
                  <td>{{ row.label }}</td>
                  <td class="text-end">
                    {{ formatCents(row.amount_cents) }}
                  </td>
                </tr>
                <tr v-if="!data.revenue_by_outlet_today.results.length">
                  <td
                    colspan="2"
                    class="text-center text-medium-emphasis py-6"
                  >
                    No charges today.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mt-2">
        <VCol
          cols="12"
          md="6"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Arrivals today
            </VCardTitle>
            <VTable density="compact">
              <thead>
                <tr>
                  <th>Guest</th>
                  <th>Mode</th>
                  <th>Time</th>
                  <th>Station</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in data.arrivals_today.results"
                  :key="String(row.id)"
                >
                  <td>{{ row.guest_name }}</td>
                  <td>{{ row.arrival_mode || '—' }}</td>
                  <td>{{ row.arrival_time || '—' }}</td>
                  <td>{{ row.arrival_station || '—' }}</td>
                </tr>
                <tr v-if="!data.arrivals_today.results.length">
                  <td
                    colspan="4"
                    class="text-center text-medium-emphasis py-6"
                  >
                    No arrivals.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Departures today
            </VCardTitle>
            <VTable density="compact">
              <thead>
                <tr>
                  <th>Guest</th>
                  <th>Ref</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in data.departures_today.results"
                  :key="String(row.id)"
                >
                  <td>{{ row.guest_name }}</td>
                  <td>{{ row.reference }}</td>
                  <td>{{ row.status }}</td>
                </tr>
                <tr v-if="!data.departures_today.results.length">
                  <td
                    colspan="3"
                    class="text-center text-medium-emphasis py-6"
                  >
                    No departures.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mt-2">
        <VCol
          cols="12"
          md="6"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Lift status
            </VCardTitle>
            <VTable density="compact">
              <thead>
                <tr>
                  <th>Lift</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in data.lift_status.results"
                  :key="String(row.id)"
                >
                  <td>{{ row.name }}</td>
                  <td>
                    <VChip
                      size="small"
                      variant="tonal"
                    >
                      {{ row.status }}
                    </VChip>
                  </td>
                </tr>
                <tr v-if="!data.lift_status.results.length">
                  <td
                    colspan="2"
                    class="text-center text-medium-emphasis py-6"
                  >
                    No lifts (or transport tables missing).
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Active weather warnings
            </VCardTitle>
            <VTable density="compact">
              <thead>
                <tr>
                  <th>Variable</th>
                  <th>Forecast</th>
                  <th>Threshold</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in data.weather_warnings.results"
                  :key="String(row.id)"
                >
                  <td>{{ row.variable }}</td>
                  <td>{{ row.forecast_value }}</td>
                  <td>{{ row.threshold }}</td>
                </tr>
                <tr v-if="!data.weather_warnings.results.length">
                  <td
                    colspan="3"
                    class="text-center text-medium-emphasis py-6"
                  >
                    No active warnings.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mt-2">
        <VCol cols="12">
          <VCard>
            <VCardTitle class="text-subtitle-1">
              Rentals out
            </VCardTitle>
            <VTable density="compact">
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>Guest</th>
                  <th>Status</th>
                  <th>Due</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in data.rentals_out.results"
                  :key="String(row.id)"
                >
                  <td>{{ row.reference || row.id }}</td>
                  <td>{{ row.guest_name || '—' }}</td>
                  <td>{{ row.status }}</td>
                  <td>{{ row.due_on || '—' }}</td>
                </tr>
                <tr v-if="!data.rentals_out.results.length">
                  <td
                    colspan="4"
                    class="text-center text-medium-emphasis py-6"
                  >
                    Nothing out.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>
