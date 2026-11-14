<script setup lang="ts">
const props = defineProps<{
  entityData?: Record<string, any> | null
  entityRandomId?: string
}>()

const client = useHotelApiClient()
const loading = ref(true)
const folio = ref<{
  folio: Record<string, any>
  charges: Array<Record<string, any>>
  payments: Array<Record<string, any>>
  balance_cents?: number
} | null>(null)

const load = async () => {
  loading.value = true
  const stayId = Number(props.entityRandomId || props.entityData?.id)
  if (!stayId) {
    loading.value = false

    return
  }

  folio.value = await client.stayFolio(stayId) as any
  loading.value = false
}

onMounted(load)
watch(() => props.entityRandomId, load)
</script>

<template>
  <div>
    <div
      v-if="loading"
      class="py-8 d-flex justify-center"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
    </div>

    <template v-else-if="folio">
      <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
        <VChip
          size="small"
          variant="tonal"
          :color="folio.folio.status === 'open' ? 'success' : 'default'"
        >
          {{ folio.folio.reference }} / {{ folio.folio.status }}
        </VChip>
        <div class="d-flex align-center gap-3">
          <div class="text-h6">
            Balance {{ formatCents(folio.balance_cents ?? 0) }}
          </div>
          <VBtn
            v-if="folio.folio?.id"
            size="small"
            color="primary"
            variant="tonal"
            :to="`/folios/view/${folio.folio.id}`"
          >
            Open folio
          </VBtn>
        </div>
      </div>

      <VCard class="mb-4">
        <VCardTitle class="text-body-1">
          Charges
        </VCardTitle>
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
              v-for="charge in folio.charges"
              :key="charge.id"
            >
              <td>{{ charge.description || charge.source || charge.id }}</td>
              <td class="text-end">
                {{ formatCents(charge.amount_cents ?? charge.gross_cents) }}
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>

      <VCard>
        <VCardTitle class="text-body-1">
          Payments
        </VCardTitle>
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
              v-for="payment in folio.payments"
              :key="payment.id"
            >
              <td>{{ payment.method || payment.id }}</td>
              <td class="text-end">
                {{ formatCents(payment.amount_cents) }}
              </td>
            </tr>
            <tr v-if="!folio.payments?.length">
              <td
                colspan="2"
                class="text-medium-emphasis text-center"
              >
                No payments yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </template>

    <VAlert
      v-else
      type="info"
      variant="tonal"
    >
      Folio not available.
    </VAlert>
  </div>
</template>
