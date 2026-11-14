<script setup lang="ts">
import type { EquipmentItem } from '@/composables/api-clients/useRentalApiClient'

definePageMeta({ navActiveLink: 'rental' })
useHead({ title: 'Rent out' })

const client = useRentalApiClient()
const loading = ref(false)
const saving = ref(false)
const available = ref<EquipmentItem[]>([])
const selected = ref<number[]>([])
const form = ref({
  customer_type: 'walk_in' as 'walk_in' | 'room',
  guest_name: '',
  stay_id: null as number | null,
  days: 1,
  deposit: 0,
  pay_now: true,
  payment_method: 'cash' as 'cash' | 'card',
  notes: '',
})

async function load() {
  loading.value = true
  const res = await client.listItems({ available_only: true })
  // Prefer active ok items; API may ignore available_only — filter client-side
  available.value = (res?.results ?? []).filter(i => i.is_active && i.condition === 'ok')
  loading.value = false
}

async function submit() {
  if (!selected.value.length || !form.value.guest_name.trim())
    return
  if (form.value.customer_type === 'room' && !form.value.stay_id)
    return

  saving.value = true
  const rental = await client.rentOut({
    customer_type: form.value.customer_type,
    guest_name: form.value.guest_name.trim(),
    stay_id: form.value.customer_type === 'room' ? form.value.stay_id : undefined,
    equipment_item_ids: selected.value,
    days: form.value.days,
    deposit_cents: majorToCents(form.value.deposit),
    pay_now: form.value.customer_type === 'walk_in' ? form.value.pay_now : undefined,
    payment_method: form.value.payment_method,
    notes: form.value.notes || undefined,
  })
  saving.value = false
  showAlertDialog('Rented out', `Contract ${rental.reference} created.`, 'OK')
  await navigateTo(`/rental/contracts/view/${rental.id}`)
}

onMounted(load)
</script>

<template>
  <div>
    <div class="mb-4">
      <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/rental' }" class="mb-1 px-0">
        Rental
      </VBtn>
      <h2 class="text-h5 mb-1">
        Rent out
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Select available items. Room guests charge to folio; walk-in can pay now.
      </p>
    </div>

    <VRow>
      <VCol cols="12" md="5">
        <VCard>
          <VCardTitle class="text-subtitle-1">
            Guest
          </VCardTitle>
          <VCardText class="d-flex flex-column gap-3">
            <AppSelect
              v-model="form.customer_type"
              :items="[
                { title: 'Walk-in', value: 'walk_in' },
                { title: 'In-house (room)', value: 'room' },
              ]"
              item-title="title"
              item-value="value"
              label="Customer type"
            />
            <AppTextField v-model="form.guest_name" label="Guest name" />
            <AppTextField
              v-if="form.customer_type === 'room'"
              v-model.number="form.stay_id"
              type="number"
              min="1"
              label="Stay ID"
            />
            <AppTextField v-model.number="form.days" type="number" min="1" label="Days" />
            <AppTextField v-model.number="form.deposit" type="number" step="0.01" min="0" label="Deposit (EUR)" />
            <template v-if="form.customer_type === 'walk_in'">
              <VSwitch v-model="form.pay_now" label="Pay now" color="primary" hide-details />
              <AppSelect
                v-if="form.pay_now"
                v-model="form.payment_method"
                :items="[
                  { title: 'Cash', value: 'cash' },
                  { title: 'Card', value: 'card' },
                ]"
                item-title="title"
                item-value="value"
                label="Payment method"
              />
            </template>
            <AppTextField v-model="form.notes" label="Notes" />
            <VBtn
              color="primary"
              :loading="saving"
              :disabled="!selected.length || !form.guest_name.trim() || (form.customer_type === 'room' && !form.stay_id)"
              @click="submit"
            >
              Confirm rent out
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="7">
        <VCard>
          <VCardTitle class="d-flex align-center justify-space-between">
            <span class="text-subtitle-1">Available items</span>
            <VBtn size="small" variant="tonal" :loading="loading" @click="load">
              Refresh
            </VBtn>
          </VCardTitle>
          <VCardText>
            <VCheckbox
              v-for="item in available"
              :key="item.id"
              v-model="selected"
              :label="`${item.item_code}${item.size ? ' · ' + item.size : ''} (#${item.id})`"
              :value="item.id"
              hide-details
              class="mb-1"
            />
            <div v-if="!loading && !available.length" class="text-medium-emphasis py-4">
              No available items.
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
