<script setup lang="ts">
import type { FolioRow } from '@/composables/api-clients/useFolioApiClient'

definePageMeta({ navActiveLink: 'folios' })
useHead({ title: 'Folios' })

const client = useFolioApiClient()
const loading = ref(false)
const rows = ref<FolioRow[]>([])
const status = ref<string>('')
const reference = ref('')

const statusOptions = [
  { title: 'All', value: '' },
  { title: 'Open', value: 'open' },
  { title: 'Closed', value: 'closed' },
]

async function load() {
  loading.value = true
  const params: Record<string, unknown> = {}
  if (status.value)
    params.status = status.value
  if (reference.value.trim())
    params.reference = reference.value.trim()

  const response = await client.list(params)
  rows.value = response?.results ?? []
  loading.value = false
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <h2 class="text-h5 mb-1">
          Guest folios
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Open bills, balance, charge to room, payments and voids.
        </p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <VBtn
          color="primary"
          variant="tonal"
          prepend-icon="tabler-door"
          :to="{ path: '/folios/charge-to-room' }"
        >
          Charge to room
        </VBtn>
        <VBtn
          color="primary"
          prepend-icon="tabler-refresh"
          :loading="loading"
          @click="load"
        >
          Refresh
        </VBtn>
      </div>
    </div>

    <VCard class="mb-4">
      <VCardText class="d-flex flex-wrap gap-3">
        <AppSelect
          v-model="status"
          :items="statusOptions"
          item-title="title"
          item-value="value"
          label="Status"
          density="compact"
          style="min-inline-size: 160px"
          @update:model-value="load"
        />
        <AppTextField
          v-model="reference"
          label="Reference"
          density="compact"
          style="min-inline-size: 220px"
          clearable
          @keyup.enter="load"
        />
        <VBtn
          variant="tonal"
          @click="load"
        >
          Filter
        </VBtn>
      </VCardText>
    </VCard>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th>
            <th>Reference</th>
            <th>Status</th>
            <th>Stay</th>
            <th class="text-end">
              Balance
            </th>
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
            <td>{{ row.id }}</td>
            <td>{{ row.reference || '—' }}</td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="row.status === 'open' ? 'success' : 'default'"
              >
                {{ row.status }}
              </VChip>
            </td>
            <td>
              <NuxtLink
                v-if="row.stay_id"
                :to="`/hotel/stays/view/${row.stay_id}`"
                class="text-primary"
              >
                Stay {{ row.stay_id }}
              </NuxtLink>
              <span
                v-else
                class="text-medium-emphasis"
              >—</span>
            </td>
            <td class="text-end">
              {{ formatCents(row.balance_cents ?? 0) }}
            </td>
            <td class="text-end">
              <VBtn
                size="small"
                variant="tonal"
                color="primary"
                :to="`/folios/view/${row.id}`"
              >
                Open
              </VBtn>
            </td>
          </tr>
          <tr v-if="!loading && rows.length === 0">
            <td
              colspan="6"
              class="text-center text-medium-emphasis py-8"
            >
              No folios match the filter.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
