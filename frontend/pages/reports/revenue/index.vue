<script setup lang="ts">
import type { RevenueReport } from '@/composables/api-clients/useReportsApiClient'

definePageMeta({ navActiveLink: 'reports' })
useHead({ title: 'Revenue reports' })

const client = useReportsApiClient()
const loading = ref(false)
const downloading = ref(false)
const tab = ref('outlet')

function defaultFrom(): string {
  const d = new Date()
  d.setDate(1)

  return d.toISOString().slice(0, 10)
}

function defaultTo(): string {
  return new Date().toISOString().slice(0, 10)
}

const fromDate = ref(defaultFrom())
const toDate = ref(defaultTo())
const report = ref<RevenueReport | null>(null)

const rangeParams = computed(() => ({
  from_date: fromDate.value,
  to_date: toDate.value,
}))

async function load() {
  if (!fromDate.value || !toDate.value)
    return
  loading.value = true
  if (tab.value === 'outlet')
    report.value = await client.revenueByOutlet(rangeParams.value)
  else if (tab.value === 'service')
    report.value = await client.revenueByService(rangeParams.value)
  else
    report.value = await client.revenueByPaymentMethod(rangeParams.value)
  loading.value = false
}

async function downloadCsv() {
  downloading.value = true
  if (tab.value === 'outlet')
    await client.downloadRevenueByOutletCsv(rangeParams.value)
  else if (tab.value === 'service')
    await client.downloadRevenueByServiceCsv(rangeParams.value)
  else
    await client.downloadRevenueByPaymentMethodCsv(rangeParams.value)
  downloading.value = false
}

watch(tab, load)
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
          Revenue
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Non-voided charges (and payments for method report). Amounts in integer cents.
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

    <VTabs
      v-model="tab"
      class="mb-4"
    >
      <VTab value="outlet">
        By outlet
      </VTab>
      <VTab value="service">
        By service / source
      </VTab>
      <VTab value="payment">
        By payment method
      </VTab>
    </VTabs>

    <VCard>
      <VCardTitle class="d-flex flex-wrap justify-space-between align-center gap-2">
        <span>
          {{ report?.basis ?? '…' }}
          <span
            v-if="report"
            class="text-body-2 text-medium-emphasis ms-2"
          >
            {{ report.from_date }} → {{ report.to_date }} · {{ report.currency }}
          </span>
        </span>
        <span
          v-if="report"
          class="text-subtitle-1"
        >
          Total {{ formatCents(report.total_cents) }}
        </span>
      </VCardTitle>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Key</th>
            <th>Label</th>
            <th class="text-end">
              Amount
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="3"
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
            :key="row.key"
          >
            <td>{{ row.key }}</td>
            <td>{{ row.label }}</td>
            <td class="text-end">
              {{ formatCents(row.amount_cents) }}
            </td>
          </tr>
          <tr v-if="!loading && !(report?.rows?.length)">
            <td
              colspan="3"
              class="text-center text-medium-emphasis py-8"
            >
              No rows for this range.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
