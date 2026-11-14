<script setup lang="ts">
import type { PosOrder, PosProduct } from '@/composables/api-clients/usePosApiClient'

definePageMeta({ navActiveLink: 'pos' })

const route = useRoute()
const orderId = computed(() => Number(route.params.id))
useHead({ title: computed(() => `Order ${orderId.value}`) })

const client = usePosApiClient()
const loading = ref(true)
const saving = ref(false)
const order = ref<PosOrder | null>(null)
const items = ref<Array<Record<string, any>>>([])
const products = ref<PosProduct[]>([])

const addOpen = ref(false)
const settleOpen = ref(false)
const receiptOpen = ref(false)
const receipt = ref<Record<string, any> | null>(null)
const kitchen = ref<unknown>(null)

const addForm = ref({
  product_id: null as number | null,
  quantity: 1,
  note: '',
  is_board_included: false,
  meal: 'dinner',
})
const settleMethod = ref<'cash' | 'card' | 'room'>('cash')

const boardForm = ref({ stay_id: null as number | null, meal: 'dinner' })
const boardResult = ref<Record<string, any> | null>(null)

const isOpen = computed(() =>
  order.value ? ['open', 'sent'].includes(order.value.status) : false,
)

const mealOptions = [
  { title: 'Breakfast', value: 'breakfast' },
  { title: 'Lunch', value: 'lunch' },
  { title: 'Dinner', value: 'dinner' },
]

async function load() {
  loading.value = true
  const detail = await client.showOrder(orderId.value)
  order.value = detail?.order ?? null
  items.value = detail?.items ?? []
  if (!products.value.length) {
    const prod = await client.listProducts({ active_only: true })
    products.value = prod?.results ?? []
  }
  loading.value = false
}

function openAdd() {
  addForm.value = {
    product_id: products.value[0]?.id ?? null,
    quantity: 1,
    note: '',
    is_board_included: false,
    meal: 'dinner',
  }
  addOpen.value = true
}

async function submitAdd() {
  if (!addForm.value.product_id)
    return
  saving.value = true
  const body: Record<string, unknown> = {
    product_id: addForm.value.product_id,
    quantity: addForm.value.quantity,
    note: addForm.value.note || undefined,
    is_board_included: addForm.value.is_board_included,
  }
  if (addForm.value.is_board_included)
    body.meal = addForm.value.meal

  await client.addItem(orderId.value, body as any)
  saving.value = false
  addOpen.value = false
  await load()
}

async function submitSettle() {
  saving.value = true
  const result = await client.settleOrder(orderId.value, settleMethod.value)
  saving.value = false
  settleOpen.value = false
  if (result) {
    receipt.value = result.receipt as any
    kitchen.value = result.kitchen_ticket
    receiptOpen.value = true
    showAlertDialog('Order settled', `Paid via ${settleMethod.value}.`, 'OK')
  }
  await load()
}

async function runBoardCheck() {
  if (!boardForm.value.stay_id)
    return
  boardResult.value = await client.boardCheck({
    stay_id: boardForm.value.stay_id,
    meal: boardForm.value.meal,
  })
}

function printReceipt() {
  window.print()
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
          :to="{ path: '/pos/orders' }"
          class="mb-1 px-0"
        >
          Orders
        </VBtn>
        <h2 class="text-h5 mb-1">
          Order {{ orderId }}
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Add lines then settle. Board-included lines post zero guest charge.
        </p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <VBtn
          v-if="isOpen"
          color="primary"
          variant="tonal"
          prepend-icon="tabler-plus"
          @click="openAdd"
        >
          Add item
        </VBtn>
        <VBtn
          v-if="isOpen"
          color="primary"
          prepend-icon="tabler-cash"
          @click="settleOpen = true"
        >
          Settle
        </VBtn>
      </div>
    </div>

    <div
      v-if="loading"
      class="py-12 d-flex justify-center"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
    </div>

    <template v-else-if="order">
      <VCard class="mb-4">
        <VCardText class="d-flex flex-wrap justify-space-between gap-3">
          <div>
            <VChip
              size="small"
              variant="tonal"
              :color="isOpen ? 'success' : 'default'"
            >
              {{ order.status }}
            </VChip>
            <div class="mt-2 text-body-2">
              Outlet {{ order.outlet_id }}
              <span v-if="order.table_label"> · Table {{ order.table_label }}</span>
              <span v-if="order.stay_id"> · Stay {{ order.stay_id }}</span>
              <span v-if="order.folio_id">
                ·
                <NuxtLink
                  :to="`/folios/view/${order.folio_id}`"
                  class="text-primary"
                >Folio {{ order.folio_id }}</NuxtLink>
              </span>
            </div>
          </div>
          <div class="text-end">
            <div class="text-caption text-medium-emphasis">
              Total
            </div>
            <div class="text-h5">
              {{ formatCents(order.total_cents) }}
            </div>
            <div class="text-caption">
              Tax {{ formatCents(order.tax_cents) }}
            </div>
          </div>
        </VCardText>
      </VCard>

      <VCard class="mb-4">
        <VCardTitle class="text-body-1">
          Lines
        </VCardTitle>
        <VTable density="compact">
          <thead>
            <tr>
              <th>Product</th>
              <th>Qty</th>
              <th>Board</th>
              <th class="text-end">
                Line total
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="line in items"
              :key="line.id"
            >
              <td>{{ line.description || line.product_id }}</td>
              <td>{{ line.quantity }}</td>
              <td>{{ line.is_board_included ? (line.meal || 'yes') : '—' }}</td>
              <td class="text-end">
                {{ formatCents(line.line_total_cents ?? line.total_cents ?? 0) }}
              </td>
            </tr>
            <tr v-if="!items.length">
              <td
                colspan="4"
                class="text-center text-medium-emphasis"
              >
                No items yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>

      <VCard>
        <VCardTitle class="text-body-1">
          Board check
        </VCardTitle>
        <VCardText class="d-flex flex-wrap gap-3 align-end">
          <AppTextField
            v-model.number="boardForm.stay_id"
            type="number"
            label="Stay ID"
            density="compact"
            style="min-inline-size: 140px"
          />
          <AppSelect
            v-model="boardForm.meal"
            :items="mealOptions"
            item-title="title"
            item-value="value"
            label="Meal"
            density="compact"
            style="min-inline-size: 160px"
          />
          <VBtn
            variant="tonal"
            :disabled="!boardForm.stay_id"
            @click="runBoardCheck"
          >
            Check
          </VBtn>
        </VCardText>
        <VCardText v-if="boardResult">
          <pre class="text-caption mb-0">{{ boardResult }}</pre>
        </VCardText>
      </VCard>
    </template>

    <VAlert
      v-else
      type="error"
      variant="tonal"
    >
      Order not found.
    </VAlert>

    <VDialog
      v-model="addOpen"
      max-width="520"
    >
      <VCard>
        <VCardTitle>Add item</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="addForm.product_id"
            :items="products"
            item-title="name"
            item-value="id"
            label="Product"
          />
          <AppTextField
            v-model.number="addForm.quantity"
            type="number"
            min="1"
            label="Quantity"
          />
          <AppTextField
            v-model="addForm.note"
            label="Note"
          />
          <VSwitch
            v-model="addForm.is_board_included"
            label="Board included (zero guest charge)"
            color="primary"
            hide-details
          />
          <AppSelect
            v-if="addForm.is_board_included"
            v-model="addForm.meal"
            :items="mealOptions"
            item-title="title"
            item-value="value"
            label="Meal"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="addOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!addForm.product_id || !(addForm.quantity > 0)"
            @click="submitAdd"
          >
            Add
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="settleOpen"
      max-width="420"
    >
      <VCard>
        <VCardTitle>Settle order</VCardTitle>
        <VCardText>
          <AppSelect
            v-model="settleMethod"
            :items="[
              { title: 'Cash', value: 'cash' },
              { title: 'Card', value: 'card' },
              { title: 'Charge to room', value: 'room' },
            ]"
            item-title="title"
            item-value="value"
            label="Payment method"
          />
          <VAlert
            v-if="settleMethod === 'room' && !order?.stay_id"
            type="warning"
            variant="tonal"
            density="compact"
            class="mt-3"
          >
            Room settle needs stay_id on the order.
          </VAlert>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="settleOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            @click="submitSettle"
          >
            Settle
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="receiptOpen"
      max-width="640"
      scrollable
    >
      <VCard>
        <VCardTitle>Receipt / kitchen ticket</VCardTitle>
        <VCardText>
          <div class="text-subtitle-2 mb-1">
            Receipt
          </div>
          <pre class="text-caption">{{ receipt }}</pre>
          <div class="text-subtitle-2 mt-4 mb-1">
            Kitchen ticket
          </div>
          <pre class="text-caption">{{ kitchen }}</pre>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="tonal"
            @click="printReceipt"
          >
            Print
          </VBtn>
          <VBtn
            color="primary"
            @click="receiptOpen = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
