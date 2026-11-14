<script setup lang="ts">
import type { PosOrder } from '@/composables/api-clients/usePosApiClient'

definePageMeta({ navActiveLink: 'pos' })
useHead({ title: 'POS orders' })

const client = usePosApiClient()
const loading = ref(false)
const saving = ref(false)
const orders = ref<PosOrder[]>([])
const outlets = ref<Array<{ id: number; name: string }>>([])
const status = ref('')
const openDialog = ref(false)
const form = ref({
  outlet_id: null as number | null,
  table_label: '',
  stay_id: null as number | null,
})

const statusOptions = [
  { title: 'All', value: '' },
  { title: 'Open', value: 'open' },
  { title: 'Sent', value: 'sent' },
  { title: 'Paid', value: 'paid' },
]

async function load() {
  loading.value = true
  const params: Record<string, unknown> = {}
  if (status.value)
    params.status = status.value
  const [orderRes, outletRows] = await Promise.all([
    client.listOrders(params),
    client.listOutlets(),
  ])
  orders.value = orderRes?.results ?? []
  outlets.value = (outletRows as any[]).map((o: any) => ({ id: o.id, name: o.name }))
  loading.value = false
}

function startOpen() {
  form.value = {
    outlet_id: outlets.value[0]?.id ?? null,
    table_label: '',
    stay_id: null,
  }
  openDialog.value = true
}

async function submitOpen() {
  if (!form.value.outlet_id)
    return
  saving.value = true
  const order = await client.openOrder({
    outlet_id: form.value.outlet_id,
    table_label: form.value.table_label || undefined,
    stay_id: form.value.stay_id || undefined,
  })
  saving.value = false
  openDialog.value = false
  await navigateTo(`/pos/orders/view/${order.id}`)
}

function outletName(id: number) {
  return outlets.value.find(o => o.id === id)?.name || String(id)
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
          :to="{ path: '/pos' }"
          class="mb-1 px-0"
        >
          POS
        </VBtn>
        <h2 class="text-h5 mb-1">
          Orders
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Open tickets, add items, settle cash / card / room.
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
          @click="startOpen"
        >
          New order
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
          style="max-inline-size: 220px"
          @update:model-value="load"
        />
      </VCardText>
    </VCard>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th>
            <th>Outlet</th>
            <th>Table</th>
            <th>Status</th>
            <th class="text-end">
              Total
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
            v-for="row in orders"
            v-else
            :key="row.id"
          >
            <td>{{ row.id }}</td>
            <td>{{ outletName(row.outlet_id) }}</td>
            <td>{{ row.table_label || '—' }}</td>
            <td>
              <VChip
                size="small"
                variant="tonal"
              >
                {{ row.status }}
              </VChip>
            </td>
            <td class="text-end">
              {{ formatCents(row.total_cents) }}
            </td>
            <td class="text-end">
              <VBtn
                size="small"
                variant="tonal"
                color="primary"
                :to="`/pos/orders/view/${row.id}`"
              >
                Open
              </VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !orders.length">
            <td
              colspan="6"
              class="text-center text-medium-emphasis py-8"
            >
              No orders match the filter.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog
      v-model="openDialog"
      max-width="480"
    >
      <VCard>
        <VCardTitle>New order</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="form.outlet_id"
            :items="outlets"
            item-title="name"
            item-value="id"
            label="Outlet"
          />
          <AppTextField
            v-model="form.table_label"
            label="Table (optional)"
          />
          <AppTextField
            v-model.number="form.stay_id"
            type="number"
            min="1"
            label="Stay ID (optional, for room / board)"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="openDialog = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!form.outlet_id"
            @click="submitOpen"
          >
            Open order
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
