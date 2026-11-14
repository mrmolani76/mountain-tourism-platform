<script setup lang="ts">
import type { Lift } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Lift passes' })

const client = useTransportApiClient()
const loading = ref(false)
const saving = ref(false)
const products = ref<Array<Record<string, any>>>([])
const lifts = ref<Lift[]>([])
const productOpen = ref(false)
const sellOpen = ref(false)
const productForm = ref({ product_id: null as number | null, lift_id: null as number | null, valid_days: 1, is_active: true })
const sellForm = ref({
  lift_pass_product_id: null as number | null,
  guest_name: '',
  stay_id: null as number | null,
  valid_from: new Date().toISOString().slice(0, 10),
  pay_now: false,
  payment_method: 'cash' as 'cash' | 'card',
})

async function load() {
  loading.value = true
  const [p, l] = await Promise.all([
    client.listLiftPassProducts(),
    client.listLifts(),
  ])
  products.value = p?.results ?? []
  lifts.value = l?.results ?? []
  loading.value = false
}

async function saveProduct() {
  if (!productForm.value.product_id)
    return
  saving.value = true
  await client.createLiftPassProduct({
    product_id: productForm.value.product_id,
    lift_id: productForm.value.lift_id || undefined,
    valid_days: productForm.value.valid_days,
    is_active: productForm.value.is_active,
  })
  saving.value = false
  productOpen.value = false
  await load()
}

async function sell() {
  if (!sellForm.value.lift_pass_product_id || !sellForm.value.guest_name)
    return
  saving.value = true
  const issued = await client.sellLiftPass({
    lift_pass_product_id: sellForm.value.lift_pass_product_id,
    guest_name: sellForm.value.guest_name,
    stay_id: sellForm.value.stay_id || undefined,
    valid_from: sellForm.value.valid_from,
    pay_now: sellForm.value.pay_now,
    payment_method: sellForm.value.payment_method,
  })
  saving.value = false
  sellOpen.value = false
  showAlertDialog('Pass sold', `Issued pass #${issued?.id ?? ''}. Charge via Folio.`, 'OK')
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
        <h2 class="text-h5 mb-1">Lift passes</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Map POS products to lifts, then sell to room or walk-in.</p>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">Refresh</VBtn>
        <VBtn variant="tonal" prepend-icon="tabler-plus" @click="productOpen = true">Map product</VBtn>
        <VBtn color="primary" prepend-icon="tabler-ticket" @click="sellOpen = true">Sell pass</VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th>
            <th>POS product</th>
            <th>Lift</th>
            <th>Valid days</th>
            <th>Active</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="5" class="text-center py-8"><VProgressCircular indeterminate color="primary" /></td>
          </tr>
          <tr v-for="row in products" v-else :key="row.id">
            <td>{{ row.id }}</td>
            <td>{{ row.product_id }}</td>
            <td>{{ row.lift_id || 'Any' }}</td>
            <td>{{ row.valid_days }}</td>
            <td>{{ row.is_active ? 'Yes' : 'No' }}</td>
          </tr>
          <tr v-if="!loading && !products.length">
            <td colspan="5" class="text-center text-medium-emphasis py-8">No lift pass products mapped.</td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="productOpen" max-width="480">
      <VCard>
        <VCardTitle>Map POS product</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField v-model.number="productForm.product_id" type="number" label="POS product ID" />
          <AppSelect v-model="productForm.lift_id" :items="lifts" item-title="name" item-value="id" label="Lift (optional)" clearable />
          <AppTextField v-model.number="productForm.valid_days" type="number" min="1" label="Valid days" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="productOpen = false">Cancel</VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!productForm.product_id" @click="saveProduct">Save</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog v-model="sellOpen" max-width="480">
      <VCard>
        <VCardTitle>Sell lift pass</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="sellForm.lift_pass_product_id"
            :items="products.map(p => ({ title: `#${p.id} · product ${p.product_id} · ${p.valid_days}d`, value: p.id }))"
            item-title="title"
            item-value="value"
            label="Pass product"
          />
          <AppTextField v-model="sellForm.guest_name" label="Guest name" />
          <AppTextField v-model.number="sellForm.stay_id" type="number" label="Stay ID (room charge)" />
          <AppTextField v-model="sellForm.valid_from" type="date" label="Valid from" />
          <VSwitch v-model="sellForm.pay_now" label="Pay now (cash/card)" color="primary" hide-details />
          <AppSelect
            v-if="sellForm.pay_now"
            v-model="sellForm.payment_method"
            :items="[{ title: 'Cash', value: 'cash' }, { title: 'Card', value: 'card' }]"
            item-title="title"
            item-value="value"
            label="Method"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="sellOpen = false">Cancel</VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!sellForm.lift_pass_product_id || !sellForm.guest_name"
            @click="sell"
          >
            Sell
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
