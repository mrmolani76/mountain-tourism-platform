<script setup lang="ts">
import type { PosProduct } from '@/composables/api-clients/usePosApiClient'

definePageMeta({ navActiveLink: 'pos' })
useHead({ title: 'POS products' })

const client = usePosApiClient()
const loading = ref(false)
const saving = ref(false)
const products = ref<PosProduct[]>([])
const categories = ref<Array<{ id: number; name: string }>>([])
const dialogOpen = ref(false)
const form = ref({
  name: '',
  code: '',
  category_id: null as number | null,
  price: 0,
  tax_rate_bp: 0,
  is_active: true,
})

async function load() {
  loading.value = true
  const [prodRes, catRes] = await Promise.all([
    client.listProducts({ active_only: false }),
    client.listCategories(),
  ])
  products.value = prodRes?.results ?? []
  categories.value = (catRes?.results ?? []).map((c: any) => ({ id: c.id, name: c.name }))
  loading.value = false
}

function openCreate() {
  form.value = { name: '', code: '', category_id: null, price: 0, tax_rate_bp: 0, is_active: true }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  await client.createProduct({
    name: form.value.name,
    code: form.value.code || null,
    category_id: form.value.category_id,
    price_cents: majorToCents(form.value.price),
    tax_rate_bp: form.value.tax_rate_bp,
    is_active: form.value.is_active,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Product saved', 'Product is available for POS orders.', 'OK')
  await load()
}

function categoryName(id: number | null) {
  if (!id)
    return '—'
  return categories.value.find(c => c.id === id)?.name || String(id)
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
          Products
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Catalog prices in EUR. Tax rate uses basis points (1000 = 10%).
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
          @click="openCreate"
        >
          Add product
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Category</th>
            <th class="text-end">
              Price
            </th>
            <th>Active</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="5"
              class="text-center py-8"
            >
              <VProgressCircular
                indeterminate
                color="primary"
              />
            </td>
          </tr>
          <tr
            v-for="row in products"
            v-else
            :key="row.id"
          >
            <td>{{ row.code || '—' }}</td>
            <td>{{ row.name }}</td>
            <td>{{ categoryName(row.category_id) }}</td>
            <td class="text-end">
              {{ formatCents(row.price_cents) }}
            </td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="row.is_active ? 'success' : 'default'"
              >
                {{ row.is_active ? 'Yes' : 'No' }}
              </VChip>
            </td>
          </tr>
          <tr v-if="!loading && !products.length">
            <td
              colspan="5"
              class="text-center text-medium-emphasis py-8"
            >
              No products yet.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog
      v-model="dialogOpen"
      max-width="520"
    >
      <VCard>
        <VCardTitle>Add product</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField
            v-model="form.name"
            label="Name"
          />
          <AppTextField
            v-model="form.code"
            label="Code (optional)"
          />
          <AppSelect
            v-model="form.category_id"
            :items="categories"
            item-title="name"
            item-value="id"
            label="Category"
            clearable
          />
          <AppTextField
            v-model.number="form.price"
            type="number"
            step="0.01"
            min="0"
            label="Price (EUR)"
          />
          <AppTextField
            v-model.number="form.tax_rate_bp"
            type="number"
            min="0"
            label="Tax rate (bp)"
          />
          <VSwitch
            v-model="form.is_active"
            label="Active"
            color="primary"
            hide-details
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="dialogOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!form.name"
            @click="save"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
