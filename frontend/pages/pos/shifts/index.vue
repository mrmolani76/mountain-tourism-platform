<script setup lang="ts">
import type { PosShift } from '@/composables/api-clients/usePosApiClient'

definePageMeta({ navActiveLink: 'pos' })
useHead({ title: 'POS shifts' })

const client = usePosApiClient()
const loading = ref(false)
const saving = ref(false)
const shifts = ref<PosShift[]>([])
const outlets = ref<Array<{ id: number; name: string }>>([])
const openDialog = ref(false)
const closeDialog = ref(false)
const closingId = ref<number | null>(null)
const openForm = ref({ outlet_id: null as number | null, opening_float: 0, notes: '' })
const closeForm = ref({ counted_cash: 0, notes: '' })

async function load() {
  loading.value = true
  const [shiftRes, outletRows] = await Promise.all([
    client.listShifts(),
    client.listOutlets(),
  ])
  shifts.value = shiftRes?.results ?? []
  outlets.value = (outletRows as any[]).map((o: any) => ({ id: o.id, name: o.name }))
  loading.value = false
}

function startOpen() {
  openForm.value = {
    outlet_id: outlets.value[0]?.id ?? null,
    opening_float: 0,
    notes: '',
  }
  openDialog.value = true
}

async function submitOpen() {
  if (!openForm.value.outlet_id)
    return
  saving.value = true
  await client.openShift({
    outlet_id: openForm.value.outlet_id,
    opening_float_cents: majorToCents(openForm.value.opening_float),
    notes: openForm.value.notes || undefined,
  })
  saving.value = false
  openDialog.value = false
  showAlertDialog('Shift opened', 'Cashier shift is open for this outlet.', 'OK')
  await load()
}

function startClose(id: number) {
  closingId.value = id
  closeForm.value = { counted_cash: 0, notes: '' }
  closeDialog.value = true
}

async function submitClose() {
  if (!closingId.value)
    return
  saving.value = true
  await client.closeShift(closingId.value, {
    counted_cash_cents: majorToCents(closeForm.value.counted_cash),
    notes: closeForm.value.notes || undefined,
  })
  saving.value = false
  closeDialog.value = false
  showAlertDialog('Shift closed', 'Counted cash recorded on the shift.', 'OK')
  await load()
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
          Cashier shifts
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Open a float at start of day, close with counted cash.
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
          Open shift
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th>
            <th>Outlet</th>
            <th>Status</th>
            <th class="text-end">
              Opening float
            </th>
            <th class="text-end">
              Counted
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
            v-for="row in shifts"
            v-else
            :key="row.id"
          >
            <td>{{ row.id }}</td>
            <td>{{ outletName(row.outlet_id) }}</td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="row.status === 'open' ? 'success' : 'default'"
              >
                {{ row.status }}
              </VChip>
            </td>
            <td class="text-end">
              {{ formatCents(row.opening_float_cents) }}
            </td>
            <td class="text-end">
              {{ row.counted_cash_cents == null ? '—' : formatCents(row.counted_cash_cents) }}
            </td>
            <td class="text-end">
              <VBtn
                v-if="row.status === 'open'"
                size="small"
                variant="tonal"
                color="warning"
                @click="startClose(row.id)"
              >
                Close
              </VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !shifts.length">
            <td
              colspan="6"
              class="text-center text-medium-emphasis py-8"
            >
              No shifts yet.
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
        <VCardTitle>Open shift</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="openForm.outlet_id"
            :items="outlets"
            item-title="name"
            item-value="id"
            label="Outlet"
          />
          <AppTextField
            v-model.number="openForm.opening_float"
            type="number"
            step="0.01"
            min="0"
            label="Opening float (EUR)"
          />
          <AppTextField
            v-model="openForm.notes"
            label="Notes (optional)"
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
            :disabled="!openForm.outlet_id"
            @click="submitOpen"
          >
            Open
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="closeDialog"
      max-width="480"
    >
      <VCard>
        <VCardTitle>Close shift</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField
            v-model.number="closeForm.counted_cash"
            type="number"
            step="0.01"
            min="0"
            label="Counted cash (EUR)"
          />
          <AppTextField
            v-model="closeForm.notes"
            label="Notes (optional)"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="closeDialog = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="warning"
            :loading="saving"
            @click="submitClose"
          >
            Close shift
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
