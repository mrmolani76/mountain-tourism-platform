<script setup lang="ts">
import type { RentalRow } from '@/composables/api-clients/useRentalApiClient'

definePageMeta({ navActiveLink: 'rental' })
useHead({ title: 'Rentals' })

const client = useRentalApiClient()
const loading = ref(false)
const rows = ref<RentalRow[]>([])
const status = ref('')

const statusOptions = [
  { title: 'All', value: '' },
  { title: 'Open', value: 'open' },
  { title: 'Late', value: 'late' },
  { title: 'Returned', value: 'returned' },
]

async function load() {
  loading.value = true
  const params: Record<string, unknown> = {}
  if (status.value)
    params.status = status.value
  const res = await client.listRentals(params)
  rows.value = res?.results ?? []
  loading.value = false
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/rental' }" class="mb-1 px-0">
          Rental
        </VBtn>
        <h2 class="text-h5 mb-1">
          All rentals
        </h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn color="primary" variant="tonal" :to="{ path: '/rental/rent-out' }">
          Rent out
        </VBtn>
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">
          Refresh
        </VBtn>
      </div>
    </div>

    <VCard class="mb-4">
      <VCardText>
        <AppSelect
          v-model="status"
          :items="statusOptions"
          item-title="title"
          item-value="value"
          label="Status"
          density="compact"
          style="max-inline-size: 200px"
          @update:model-value="load"
        />
      </VCardText>
    </VCard>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Ref</th>
            <th>Guest</th>
            <th>Type</th>
            <th>Due</th>
            <th>Status</th>
            <th class="text-end">
              Actions
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" />
            </td>
          </tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ row.reference }}</td>
            <td>{{ row.guest_name }}</td>
            <td>{{ row.customer_type }}</td>
            <td>{{ row.due_on || '—' }}</td>
            <td>
              <VChip size="small" variant="tonal">
                {{ row.status }}
              </VChip>
            </td>
            <td class="text-end">
              <VBtn size="small" variant="tonal" color="primary" :to="`/rental/contracts/view/${row.id}`">
                Open
              </VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td colspan="6" class="text-center text-medium-emphasis py-8">
              No rentals match.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
