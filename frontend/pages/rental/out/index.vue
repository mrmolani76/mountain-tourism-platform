<script setup lang="ts">
definePageMeta({ navActiveLink: 'rental' })
useHead({ title: 'Currently out' })

const client = useRentalApiClient()
const outletClient = usePosApiClient()
const loading = ref(false)
const saving = ref(false)
const overdueOnly = ref(false)
const rows = ref<Array<Record<string, any>>>([])
const outlets = ref<Array<Record<string, any>>>([])
const returnOpen = ref(false)
const returningId = ref<number | null>(null)
const returnForm = ref({
  condition_on_return: 'ok',
  damage_fee: 0,
  outlet_id: null as number | null,
})
const rentalOutlets = computed(() => outlets.value.filter(outlet =>
  outlet.is_active !== false && outlet.outlet_type === 'rental',
))
const outletOptions = computed(() => rentalOutlets.value.map(outlet => ({
  title: outlet.name,
  value: Number(outlet.id),
})))

async function load() {
  loading.value = true
  const [res, outletRows] = await Promise.all([
    client.currentlyOut({ overdue: overdueOnly.value ? '1' : '0' }),
    outletClient.listOutlets(),
  ])
  rows.value = res?.results ?? []
  outlets.value = outletRows
  loading.value = false
}

function startReturn(rentalItemId: number, outletId: number | null) {
  returningId.value = rentalItemId
  returnForm.value = {
    condition_on_return: 'ok',
    damage_fee: 0,
    outlet_id: outletId ?? (rentalOutlets.value.length === 1 ? Number(rentalOutlets.value[0].id) : null),
  }
  returnOpen.value = true
}

async function submitReturn() {
  if (!returningId.value || !returnForm.value.outlet_id)
    return
  saving.value = true
  await client.returnItem(returningId.value, {
    outlet_id: Number(returnForm.value.outlet_id),
    condition_on_return: returnForm.value.condition_on_return,
    damage_fee_cents: majorToCents(returnForm.value.damage_fee),
  })
  saving.value = false
  returnOpen.value = false
  showAlertDialog('Returned', 'Item returned. Late/damage fees post to folio when due.', 'OK')
  await load()
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
          Currently out
        </h2>
      </div>
      <div class="d-flex gap-2 align-center">
        <VSwitch
          v-model="overdueOnly"
          label="Overdue only"
          color="warning"
          hide-details
          density="compact"
          @update:model-value="load"
        />
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">
          Refresh
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Ref</th>
            <th>Guest</th>
            <th>Item</th>
            <th>Due</th>
            <th>Overdue</th>
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
          <tr v-for="row in rows" v-else :key="row.rental_item_id">
            <td>
              <NuxtLink :to="`/rental/contracts/view/${row.rental_id}`">
                {{ row.reference }}
              </NuxtLink>
            </td>
            <td>{{ row.guest_name }}</td>
            <td>{{ row.item_code }}</td>
            <td>{{ row.due_on }}</td>
            <td>
              <VChip size="small" variant="tonal" :color="row.overdue ? 'error' : 'success'">
                {{ row.overdue ? 'Yes' : 'No' }}
              </VChip>
            </td>
            <td class="text-end">
              <VBtn size="small" color="primary" variant="tonal" @click="startReturn(row.rental_item_id, row.outlet_id ?? null)">
                Return
              </VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td colspan="6" class="text-center text-medium-emphasis py-8">
              Nothing currently out.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="returnOpen" max-width="480">
      <VCard>
        <VCardTitle>Return item</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="returnForm.condition_on_return"
            :items="[
              { title: 'OK', value: 'ok' },
              { title: 'Damaged', value: 'damaged' },
              { title: 'Retired', value: 'retired' },
            ]"
            item-title="title"
            item-value="value"
            label="Condition on return"
          />
          <AppSelect
            v-model="returnForm.outlet_id"
            :items="outletOptions"
            item-title="title"
            item-value="value"
            label="Rental outlet"
          />
          <AppTextField
            v-model.number="returnForm.damage_fee"
            type="number"
            step="0.01"
            min="0"
            label="Damage fee (EUR)"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="returnOpen = false">
            Cancel
          </VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!returnForm.outlet_id" @click="submitReturn">
            Confirm return
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
