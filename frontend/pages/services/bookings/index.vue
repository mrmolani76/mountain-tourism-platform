<script setup lang="ts">
import type { BookingRow } from '@/composables/api-clients/useServicesApiClient'

definePageMeta({ navActiveLink: 'services' })
useHead({ title: 'Service bookings' })

const client = useServicesApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<BookingRow[]>([])

async function load() {
  loading.value = true
  const res = await client.listBookings()
  rows.value = res?.results ?? []
  loading.value = false
}

async function cancel(id: number) {
  saving.value = true
  await client.cancelBooking(id)
  saving.value = false
  showAlertDialog('Cancelled', 'Booking cancelled. Folio charge voided when open.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/services' }" class="mb-1 px-0">
          Services
        </VBtn>
        <h2 class="text-h5 mb-1">
          Bookings
        </h2>
      </div>
      <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">
        Refresh
      </VBtn>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th>
            <th>Guest</th>
            <th>Slot</th>
            <th>Pax</th>
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
            <td>{{ row.id }}</td>
            <td>{{ row.guest_name }}</td>
            <td>
              <NuxtLink :to="`/services/slots/view/${row.slot_id}`">
                #{{ row.slot_id }}
              </NuxtLink>
            </td>
            <td>{{ row.participants_count }}</td>
            <td>
              <VChip size="small" variant="tonal">
                {{ row.status }}
              </VChip>
            </td>
            <td class="text-end">
              <VBtn
                v-if="row.status === 'booked'"
                size="small"
                color="warning"
                variant="tonal"
                :loading="saving"
                @click="cancel(row.id)"
              >
                Cancel
              </VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td colspan="6" class="text-center text-medium-emphasis py-8">
              No bookings yet.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
