<script setup lang="ts">
import type { RentalRow } from '@/composables/api-clients/useRentalApiClient'

definePageMeta({ navActiveLink: 'rental' })

const route = useRoute()
const rentalId = computed(() => Number(route.params.id))
useHead({ title: computed(() => `Rental ${rentalId.value}`) })

const client = useRentalApiClient()
const outletClient = usePosApiClient()
const loading = ref(true)
const saving = ref(false)
const rental = ref<RentalRow | null>(null)
const items = ref<Array<Record<string, any>>>([])
const outlets = ref<Array<Record<string, any>>>([])
const returnOpen = ref(false)
const returningId = ref<number | null>(null)
const returnForm = ref({ condition_on_return: 'ok', damage_fee: 0, outlet_id: null as number | null })
const rentalOutlets = computed(() => outlets.value.filter(outlet =>
  outlet.is_active !== false && outlet.outlet_type === 'rental',
))
const outletOptions = computed(() => rentalOutlets.value.map(outlet => ({
  title: outlet.name,
  value: Number(outlet.id),
})))

async function load() {
  loading.value = true
  const [detail, outletRows] = await Promise.all([
    client.showRental(rentalId.value),
    outletClient.listOutlets(),
  ])
  rental.value = detail.rental
  items.value = detail.items ?? []
  outlets.value = outletRows
  loading.value = false
}

function startReturn(id: number) {
  returningId.value = id
  returnForm.value = {
    condition_on_return: 'ok',
    damage_fee: 0,
    outlet_id: rental.value?.outlet_id
      ?? (rentalOutlets.value.length === 1 ? Number(rentalOutlets.value[0].id) : null),
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
  showAlertDialog('Returned', 'Item returned.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="mb-4">
      <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/rental/contracts' }" class="mb-1 px-0">
        Rentals
      </VBtn>
      <h2 class="text-h5 mb-1">
        {{ rental?.reference || `Rental #${rentalId}` }}
      </h2>
      <p v-if="rental" class="text-body-2 text-medium-emphasis mb-0">
        {{ rental.guest_name }} · {{ rental.customer_type }} · {{ rental.status }} · due {{ rental.due_on || '—' }}
      </p>
    </div>

    <div v-if="loading" class="text-center py-12">
      <VProgressCircular indeterminate color="primary" />
    </div>

    <template v-else-if="rental">
      <VRow class="mb-4">
        <VCol cols="12" md="4">
          <VCard>
            <VCardText>
              <div class="text-caption text-medium-emphasis">
                Deposit
              </div>
              <div class="text-h6">
                {{ formatCents(rental.deposit_cents) }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" md="4">
          <VCard>
            <VCardText>
              <div class="text-caption text-medium-emphasis">
                Stay / Folio
              </div>
              <div class="text-body-1">
                Stay {{ rental.stay_id || '—' }} · Folio
                <NuxtLink v-if="rental.folio_id" :to="`/folios/view/${rental.folio_id}`">
                  #{{ rental.folio_id }}
                </NuxtLink>
                <span v-else>—</span>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VCard>
        <VCardTitle class="text-subtitle-1">
          Lines
        </VCardTitle>
        <VTable density="compact">
          <thead>
            <tr>
              <th>Item ID</th>
              <th>Status</th>
              <th class="text-end">
                Days
              </th>
              <th class="text-end">
                Line
              </th>
              <th class="text-end">
                Late / damage
              </th>
              <th class="text-end">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="line in items" :key="line.id">
              <td>{{ line.equipment_item_id }}</td>
              <td>{{ line.status }}</td>
              <td class="text-end">
                {{ line.days }}
              </td>
              <td class="text-end">
                {{ formatCents(line.line_total_cents) }}
              </td>
              <td class="text-end">
                {{ formatCents(line.late_fee_cents) }} / {{ formatCents(line.damage_fee_cents) }}
              </td>
              <td class="text-end">
                <VBtn
                  v-if="line.status === 'out'"
                  size="small"
                  variant="tonal"
                  color="primary"
                  @click="startReturn(line.id)"
                >
                  Return
                </VBtn>
              </td>
            </tr>
            <tr v-if="!items.length">
              <td colspan="6" class="text-center text-medium-emphasis py-8">
                No lines.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </template>

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
            label="Condition"
          />
          <AppSelect
            v-model="returnForm.outlet_id"
            :items="outletOptions"
            item-title="title"
            item-value="value"
            label="Rental outlet"
            :disabled="Boolean(rental?.outlet_id)"
          />
          <AppTextField v-model.number="returnForm.damage_fee" type="number" step="0.01" min="0" label="Damage fee (EUR)" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="returnOpen = false">
            Cancel
          </VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!returnForm.outlet_id" @click="submitReturn">
            Confirm
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
