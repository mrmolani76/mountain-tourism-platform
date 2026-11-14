<script setup lang="ts">
import type { ChargeToRoomMatch } from '@/composables/api-clients/useFolioApiClient'

definePageMeta({ navActiveLink: 'folios' })
useHead({ title: 'Charge to room' })

const client = useFolioApiClient()
const searching = ref(false)
const posting = ref(false)
const roomNumber = ref('')
const guest = ref('')
const matches = ref<ChargeToRoomMatch[]>([])
const selectedStayId = ref<number | null>(null)

const form = ref({
  description: '',
  amount: 0,
  tax: 0,
})

async function search() {
  searching.value = true
  selectedStayId.value = null
  const response = await client.searchChargeToRoom({
    room_number: roomNumber.value.trim() || undefined,
    guest: guest.value.trim() || undefined,
  })
  matches.value = response?.results ?? []
  if (matches.value.length === 1)
    selectedStayId.value = matches.value[0].stay_id
  searching.value = false
}

async function postCharge() {
  if (!selectedStayId.value)
    return

  posting.value = true
  const charge = await client.chargeToRoom({
    stay_id: selectedStayId.value,
    description: form.value.description,
    amount_cents: majorToCents(form.value.amount),
    tax_cents: majorToCents(form.value.tax),
    source_type: 'other',
  })
  posting.value = false

  const match = matches.value.find(m => m.stay_id === selectedStayId.value)
  showAlertDialog(
    'Charged to room',
    `Charge #${charge.id} posted${match?.folio_id ? ` on folio ${match.folio_id}` : ''}.`,
    'OK',
  )

  form.value = { description: '', amount: 0, tax: 0 }
  if (match?.folio_id)
    await navigateTo(`/folios/view/${match.folio_id}`)
}
</script>

<template>
  <div>
    <div class="mb-4">
      <VBtn
        variant="text"
        prepend-icon="tabler-arrow-left"
        :to="{ path: '/folios' }"
        class="mb-1 px-0"
      >
        Folios
      </VBtn>
      <h2 class="text-h5 mb-1">
        Charge to room
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Search in-house stays, pick an explicit stay, then post a charge to its folio.
      </p>
    </div>

    <VRow>
      <VCol
        cols="12"
        md="5"
      >
        <VCard>
          <VCardTitle class="text-body-1">
            Find stay
          </VCardTitle>
          <VCardText class="d-flex flex-column gap-3">
            <AppTextField
              v-model="roomNumber"
              label="Room number"
              @keyup.enter="search"
            />
            <AppTextField
              v-model="guest"
              label="Guest name"
              @keyup.enter="search"
            />
            <VBtn
              color="primary"
              :loading="searching"
              :disabled="!roomNumber.trim() && !guest.trim()"
              @click="search"
            >
              Search
            </VBtn>
          </VCardText>
        </VCard>

        <VCard class="mt-4">
          <VCardTitle class="text-body-1">
            Matches ({{ matches.length }})
          </VCardTitle>
          <VList
            v-if="matches.length"
            density="compact"
            select-strategy="single-independent"
          >
            <VListItem
              v-for="row in matches"
              :key="row.stay_id"
              :active="selectedStayId === row.stay_id"
              :title="row.guest_name || `Stay ${row.stay_id}`"
              :subtitle="`Room ${row.room_number || '—'} · Stay ${row.stay_id}${row.folio_id ? ` · Folio ${row.folio_id}` : ''}`"
              @click="selectedStayId = row.stay_id"
            >
              <template #prepend>
                <VRadio
                  :model-value="selectedStayId"
                  :value="row.stay_id"
                  color="primary"
                  @click.stop="selectedStayId = row.stay_id"
                />
              </template>
            </VListItem>
          </VList>
          <VCardText
            v-else
            class="text-medium-emphasis"
          >
            Search by room and/or guest. Always pick a stay before posting.
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="7"
      >
        <VCard>
          <VCardTitle class="text-body-1">
            Charge details
          </VCardTitle>
          <VCardText class="d-flex flex-column gap-3">
            <VAlert
              v-if="!selectedStayId"
              type="info"
              variant="tonal"
              density="compact"
            >
              Select a stay from the matches list. No silent first-match.
            </VAlert>
            <AppTextField
              v-model="form.description"
              label="Description"
              :disabled="!selectedStayId"
            />
            <AppTextField
              v-model.number="form.amount"
              type="number"
              step="0.01"
              min="0.01"
              label="Amount (EUR)"
              :disabled="!selectedStayId"
            />
            <AppTextField
              v-model.number="form.tax"
              type="number"
              step="0.01"
              min="0"
              label="Tax (EUR)"
              :disabled="!selectedStayId"
            />
          </VCardText>
          <VCardActions>
            <VSpacer />
            <VBtn
              color="primary"
              :loading="posting"
              :disabled="!selectedStayId || !form.description || !(form.amount > 0)"
              @click="postCharge"
            >
              Post to folio
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
