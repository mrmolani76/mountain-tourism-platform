<script setup lang="ts">
import type { FolioDetail } from '@/composables/api-clients/useFolioApiClient'

definePageMeta({ navActiveLink: 'folios' })

const route = useRoute()
const folioId = computed(() => Number(route.params.id))
useHead({ title: computed(() => `Folio ${folioId.value}`) })

const client = useFolioApiClient()
const loading = ref(true)
const detail = ref<FolioDetail | null>(null)
const invoice = ref<Record<string, any> | null>(null)
const showInvoice = ref(false)

const payOpen = ref(false)
const chargeOpen = ref(false)
const voidOpen = ref(false)
const voidTarget = ref<{ kind: 'charge' | 'payment'; id: number } | null>(null)
const saving = ref(false)

const payForm = ref({ method: 'cash' as 'cash' | 'card', amount: 0, reference: '' })
const chargeForm = ref({ description: '', amount: 0, tax: 0 })
const voidReason = ref('')

const isOpen = computed(() => detail.value?.folio.status === 'open')

async function load() {
  loading.value = true
  detail.value = await client.show(folioId.value)
  loading.value = false
}

async function loadInvoice() {
  invoice.value = await client.invoice(folioId.value)
  showInvoice.value = true
}

function openPay() {
  const balance = Math.max(0, detail.value?.balance_cents ?? 0)
  payForm.value = { method: 'cash', amount: centsToMajor(balance), reference: '' }
  payOpen.value = true
}

function openCharge() {
  chargeForm.value = { description: '', amount: 0, tax: 0 }
  chargeOpen.value = true
}

function openVoid(kind: 'charge' | 'payment', id: number) {
  voidTarget.value = { kind, id }
  voidReason.value = ''
  voidOpen.value = true
}

async function submitPay() {
  saving.value = true
  await client.recordPayment(folioId.value, {
    method: payForm.value.method,
    amount_cents: majorToCents(payForm.value.amount),
    reference: payForm.value.reference || undefined,
  })
  payOpen.value = false
  saving.value = false
  showAlertDialog('Payment recorded', 'Payment posted. It cannot be edited.', 'OK')
  await load()
}

async function submitCharge() {
  saving.value = true
  await client.postCharge(folioId.value, {
    description: chargeForm.value.description,
    amount_cents: majorToCents(chargeForm.value.amount),
    tax_cents: majorToCents(chargeForm.value.tax),
    source_type: 'other',
  })
  chargeOpen.value = false
  saving.value = false
  showAlertDialog('Charge posted', 'Charge is append-only. Void if wrong.', 'OK')
  await load()
}

async function submitVoid() {
  if (!voidTarget.value || !voidReason.value.trim())
    return

  saving.value = true
  const body: Record<string, unknown> = { reason: voidReason.value.trim() }
  if (voidTarget.value.kind === 'charge')
    body.charge_id = voidTarget.value.id
  else
    body.payment_id = voidTarget.value.id

  await client.voidEntry(folioId.value, body as any)
  voidOpen.value = false
  saving.value = false
  showAlertDialog('Voided', 'Entry voided with reason. Original row kept.', 'OK')
  await load()
}

function printInvoice() {
  window.print()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4 no-print">
      <div>
        <VBtn
          variant="text"
          prepend-icon="tabler-arrow-left"
          :to="{ path: '/folios' }"
          class="mb-1 px-0"
        >
          Folios
        </VBtn>
        <h2 class="text-h5 mb-1">
          Folio {{ folioId }}
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Charges and payments are append-only. Void with a reason.
        </p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <VBtn
          variant="tonal"
          prepend-icon="tabler-file-invoice"
          :loading="loading"
          @click="loadInvoice"
        >
          Invoice
        </VBtn>
        <VBtn
          v-if="isOpen"
          color="primary"
          variant="tonal"
          prepend-icon="tabler-plus"
          @click="openCharge"
        >
          Add charge
        </VBtn>
        <VBtn
          v-if="isOpen"
          color="primary"
          prepend-icon="tabler-cash"
          @click="openPay"
        >
          Record payment
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

    <template v-else-if="detail">
      <VCard class="mb-4">
        <VCardText class="d-flex flex-wrap align-center justify-space-between gap-3">
          <div>
            <div class="text-caption text-medium-emphasis">
              Reference
            </div>
            <div class="text-h6">
              {{ detail.folio.reference || `Folio #${detail.folio.id}` }}
            </div>
            <VChip
              class="mt-2"
              size="small"
              variant="tonal"
              :color="isOpen ? 'success' : 'default'"
            >
              {{ detail.folio.status }}
            </VChip>
          </div>
          <div class="text-end">
            <div class="text-caption text-medium-emphasis">
              Balance
            </div>
            <div class="text-h4">
              {{ formatCents(detail.balance_cents) }}
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ detail.folio.currency || 'EUR' }}
            </div>
          </div>
        </VCardText>
      </VCard>

      <VCard class="mb-4">
        <VCardTitle class="text-body-1">
          Charges
        </VCardTitle>
        <VTable density="compact">
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Source</th>
              <th class="text-end">
                Amount
              </th>
              <th>Status</th>
              <th class="text-end no-print">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="charge in detail.charges"
              :key="charge.id"
            >
              <td>{{ charge.posted_on }}</td>
              <td>{{ charge.description }}</td>
              <td>{{ charge.source_type }}</td>
              <td class="text-end">
                {{ formatCents(charge.amount_cents) }}
              </td>
              <td>
                <VChip
                  v-if="charge.is_voided"
                  size="small"
                  color="error"
                  variant="tonal"
                >
                  Voided
                </VChip>
                <span
                  v-else
                  class="text-medium-emphasis"
                >Posted</span>
              </td>
              <td class="text-end no-print">
                <VBtn
                  v-if="isOpen && !charge.is_voided"
                  size="small"
                  color="error"
                  variant="text"
                  @click="openVoid('charge', charge.id)"
                >
                  Void
                </VBtn>
              </td>
            </tr>
            <tr v-if="!detail.charges.length">
              <td
                colspan="6"
                class="text-center text-medium-emphasis"
              >
                No charges yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>

      <VCard class="mb-4">
        <VCardTitle class="text-body-1">
          Payments
        </VCardTitle>
        <VTable density="compact">
          <thead>
            <tr>
              <th>When</th>
              <th>Method</th>
              <th>Reference</th>
              <th class="text-end">
                Amount
              </th>
              <th>Status</th>
              <th class="text-end no-print">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="payment in detail.payments"
              :key="payment.id"
            >
              <td>{{ payment.created_at }}</td>
              <td class="text-capitalize">
                {{ payment.method }}
              </td>
              <td>{{ payment.reference || '—' }}</td>
              <td class="text-end">
                {{ formatCents(payment.amount_cents) }}
              </td>
              <td>
                <VChip
                  v-if="payment.is_voided"
                  size="small"
                  color="error"
                  variant="tonal"
                >
                  Voided
                </VChip>
                <span
                  v-else
                  class="text-medium-emphasis"
                >Received</span>
              </td>
              <td class="text-end no-print">
                <VBtn
                  v-if="isOpen && !payment.is_voided"
                  size="small"
                  color="error"
                  variant="text"
                  @click="openVoid('payment', payment.id)"
                >
                  Void
                </VBtn>
              </td>
            </tr>
            <tr v-if="!detail.payments.length">
              <td
                colspan="6"
                class="text-center text-medium-emphasis"
              >
                No payments yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>

      <VCard v-if="detail.voids?.length">
        <VCardTitle class="text-body-1">
          Voids
        </VCardTitle>
        <VTable density="compact">
          <thead>
            <tr>
              <th>When</th>
              <th>Charge</th>
              <th>Payment</th>
              <th>Reason</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in detail.voids"
              :key="row.id"
            >
              <td>{{ row.created_at }}</td>
              <td>{{ row.charge_id ?? '—' }}</td>
              <td>{{ row.payment_id ?? '—' }}</td>
              <td>{{ row.reason }}</td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </template>

    <VAlert
      v-else
      type="error"
      variant="tonal"
    >
      Folio not found.
    </VAlert>

    <!-- Payment dialog -->
    <VDialog
      v-model="payOpen"
      max-width="480"
      class="no-print"
    >
      <VCard>
        <VCardTitle>Record payment</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="payForm.method"
            :items="[
              { title: 'Cash', value: 'cash' },
              { title: 'Card', value: 'card' },
            ]"
            item-title="title"
            item-value="value"
            label="Method"
          />
          <AppTextField
            v-model.number="payForm.amount"
            type="number"
            step="0.01"
            min="0.01"
            label="Amount (EUR)"
          />
          <AppTextField
            v-model="payForm.reference"
            label="Reference (optional)"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="payOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!(payForm.amount > 0)"
            @click="submitPay"
          >
            Post payment
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Charge dialog -->
    <VDialog
      v-model="chargeOpen"
      max-width="480"
      class="no-print"
    >
      <VCard>
        <VCardTitle>Add charge</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField
            v-model="chargeForm.description"
            label="Description"
          />
          <AppTextField
            v-model.number="chargeForm.amount"
            type="number"
            step="0.01"
            min="0.01"
            label="Amount (EUR)"
          />
          <AppTextField
            v-model.number="chargeForm.tax"
            type="number"
            step="0.01"
            min="0"
            label="Tax (EUR)"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="chargeOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!chargeForm.description || !(chargeForm.amount > 0)"
            @click="submitCharge"
          >
            Post charge
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Void dialog -->
    <VDialog
      v-model="voidOpen"
      max-width="480"
      class="no-print"
    >
      <VCard>
        <VCardTitle>Void entry</VCardTitle>
        <VCardText>
          <VAlert
            type="warning"
            variant="tonal"
            density="compact"
            class="mb-4"
          >
            Void keeps the original row. Amount cannot be edited.
          </VAlert>
          <AppTextarea
            v-model="voidReason"
            label="Reason"
            rows="3"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="voidOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            :loading="saving"
            :disabled="!voidReason.trim()"
            @click="submitVoid"
          >
            Confirm void
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Invoice dialog -->
    <VDialog
      v-model="showInvoice"
      max-width="720"
      scrollable
    >
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between no-print">
          <span>Invoice</span>
          <div class="d-flex gap-2">
            <VBtn
              variant="tonal"
              prepend-icon="tabler-printer"
              @click="printInvoice"
            >
              Print
            </VBtn>
            <VBtn
              icon
              variant="text"
              @click="showInvoice = false"
            >
              <VIcon icon="tabler-x" />
            </VBtn>
          </div>
        </VCardTitle>
        <VCardText v-if="invoice">
          <div class="mb-4">
            <div class="text-h6">
              {{ invoice.guest_name || 'Guest' }}
            </div>
            <div class="text-body-2 text-medium-emphasis">
              Room {{ invoice.room_number || '—' }} · Folio {{ invoice.folio?.reference }}
            </div>
          </div>
          <div class="text-subtitle-2 mb-2">
            Charges
          </div>
          <VTable density="compact">
            <thead>
              <tr>
                <th>Description</th>
                <th class="text-end">
                  Amount
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="c in (invoice.charges || []).filter((x: any) => !x.is_voided)"
                :key="c.id"
              >
                <td>{{ c.description }}</td>
                <td class="text-end">
                  {{ formatCents(c.amount_cents) }}
                </td>
              </tr>
            </tbody>
          </VTable>
          <div class="text-subtitle-2 mt-4 mb-2">
            Payments
          </div>
          <VTable density="compact">
            <thead>
              <tr>
                <th>Method</th>
                <th class="text-end">
                  Amount
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="p in (invoice.payments || []).filter((x: any) => !x.is_voided)"
                :key="p.id"
              >
                <td class="text-capitalize">
                  {{ p.method }}
                </td>
                <td class="text-end">
                  {{ formatCents(p.amount_cents) }}
                </td>
              </tr>
            </tbody>
          </VTable>
          <div class="d-flex justify-space-between mt-4 text-h6">
            <span>Balance</span>
            <span>{{ formatCents(invoice.totals?.balance_cents) }}</span>
          </div>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>
