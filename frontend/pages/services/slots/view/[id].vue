<script setup lang="ts">
import type { BookingRow, SlotRow } from '@/composables/api-clients/useServicesApiClient'

definePageMeta({ navActiveLink: 'services' })

const route = useRoute()
const slotId = computed(() => Number(route.params.id))
useHead({ title: computed(() => `Slot ${slotId.value}`) })

const client = useServicesApiClient()
const loading = ref(true)
const saving = ref(false)
const slot = ref<SlotRow | null>(null)
const bookings = ref<BookingRow[]>([])
const staff = ref<Array<Record<string, any>>>([])
const outing = ref<Record<string, any> | null>(null)
const bookOpen = ref(false)
const staffId = ref<number | null>(null)
const bookForm = ref({
  guest_name: '',
  participants_count: 1,
  stay_id: null as number | null,
  pay_now: false,
  payment_method: 'cash' as 'cash' | 'card',
})

async function load() {
  loading.value = true
  const [slotsRes, bookingsRes, staffRes] = await Promise.all([
    client.listSlots(),
    client.listBookings({ slot_id: slotId.value }),
    client.listSlotStaff(slotId.value),
  ])
  slot.value = (slotsRes?.results ?? []).find(s => s.id === slotId.value) || null
  bookings.value = bookingsRes?.results ?? []
  staff.value = staffRes?.results ?? []
  loading.value = false
}

function openBook() {
  bookForm.value = {
    guest_name: '',
    participants_count: 1,
    stay_id: null,
    pay_now: false,
    payment_method: 'cash',
  }
  bookOpen.value = true
}

async function submitBook() {
  saving.value = true
  await client.createBooking({
    slot_id: slotId.value,
    guest_name: bookForm.value.guest_name,
    participants_count: bookForm.value.participants_count,
    stay_id: bookForm.value.stay_id || undefined,
    pay_now: bookForm.value.pay_now,
    payment_method: bookForm.value.payment_method,
  })
  saving.value = false
  bookOpen.value = false
  showAlertDialog('Booked', 'Booking created.', 'OK')
  await load()
}

async function assignStaff() {
  if (!staffId.value)
    return
  saving.value = true
  await client.assignSlotStaff(slotId.value, [{ staff_id: staffId.value, role: 'guide' }])
  saving.value = false
  showAlertDialog('Assigned', 'Staff assigned to slot.', 'OK')
  await load()
}

async function startOuting() {
  saving.value = true
  outing.value = await client.openOuting(slotId.value)
  saving.value = false
  showAlertDialog('Outing open', 'Guided outing started for this slot.', 'OK')
}

async function markOut(id: number) {
  saving.value = true
  const updated = await client.markParticipantOut(id)
  saving.value = false
  refreshOutingParticipant(updated)
}

async function markBack(id: number) {
  saving.value = true
  const updated = await client.markParticipantBack(id)
  saving.value = false
  refreshOutingParticipant(updated)
}

function refreshOutingParticipant(updated: Record<string, any>) {
  if (!outing.value?.participants)
    return
  outing.value.participants = outing.value.participants.map((p: any) =>
    p.id === updated.id ? { ...p, ...updated } : p,
  )
}

async function closeOuting() {
  if (!outing.value?.id)
    return
  saving.value = true
  outing.value = await client.closeOuting(outing.value.id)
  saving.value = false
  showAlertDialog('Closed', 'Outing closed.', 'OK')
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/services/slots' }" class="mb-1 px-0">
          Slots
        </VBtn>
        <h2 class="text-h5 mb-1">
          Slot #{{ slotId }}
        </h2>
        <p v-if="slot" class="text-body-2 text-medium-emphasis mb-0">
          {{ slot.starts_at }} → {{ slot.ends_at }} · {{ slot.booked_count }}/{{ slot.capacity }} · {{ slot.status }}
        </p>
      </div>
      <div v-if="slot?.status === 'scheduled'" class="d-flex flex-wrap gap-2">
        <VBtn variant="tonal" @click="openBook">
          Book
        </VBtn>
        <VBtn color="primary" variant="tonal" :loading="saving" @click="startOuting">
          Open outing
        </VBtn>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12">
      <VProgressCircular indeterminate color="primary" />
    </div>

    <template v-else>
      <VCard class="mb-4">
        <VCardTitle class="text-subtitle-1">
          Staff
        </VCardTitle>
        <VCardText class="d-flex flex-wrap gap-3 align-end">
          <AppTextField v-model.number="staffId" type="number" min="1" label="Staff ID" density="compact" style="max-inline-size: 160px" />
          <VBtn variant="tonal" :loading="saving" :disabled="!staffId" @click="assignStaff">
            Assign
          </VBtn>
          <div class="text-body-2">
            Assigned: {{ staff.length ? staff.map(s => s.staff_id).join(', ') : 'none' }}
          </div>
        </VCardText>
      </VCard>

      <VCard class="mb-4">
        <VCardTitle class="text-subtitle-1">
          Bookings
        </VCardTitle>
        <VTable density="compact">
          <thead>
            <tr>
              <th>Guest</th>
              <th>Pax</th>
              <th>Status</th>
              <th>Stay</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in bookings" :key="b.id">
              <td>{{ b.guest_name }}</td>
              <td>{{ b.participants_count }}</td>
              <td>{{ b.status }}</td>
              <td>{{ b.stay_id || '—' }}</td>
            </tr>
            <tr v-if="!bookings.length">
              <td colspan="4" class="text-center text-medium-emphasis py-6">
                No bookings on this slot.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>

      <VCard v-if="outing">
        <VCardTitle class="d-flex align-center justify-space-between">
          <span class="text-subtitle-1">Outing #{{ outing.id }} · {{ outing.status }}</span>
          <VBtn
            v-if="outing.status !== 'closed'"
            size="small"
            color="warning"
            variant="tonal"
            :loading="saving"
            @click="closeOuting"
          >
            Close outing
          </VBtn>
        </VCardTitle>
        <VTable density="compact">
          <thead>
            <tr>
              <th>Name</th>
              <th>Status</th>
              <th class="text-end">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in (outing.participants || [])" :key="p.id">
              <td>Participant #{{ p.booking_participant_id || p.id }}</td>
              <td>{{ p.status }}</td>
              <td class="text-end">
                <VBtn
                  v-if="p.status !== 'out' && outing.status !== 'closed'"
                  size="small"
                  variant="tonal"
                  class="me-1"
                  @click="markOut(p.id)"
                >
                  Out
                </VBtn>
                <VBtn
                  v-if="p.status === 'out' && outing.status !== 'closed'"
                  size="small"
                  variant="tonal"
                  color="success"
                  @click="markBack(p.id)"
                >
                  Back
                </VBtn>
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </template>

    <VDialog v-model="bookOpen" max-width="480">
      <VCard>
        <VCardTitle>Book slot</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField v-model="bookForm.guest_name" label="Guest name" />
          <AppTextField v-model.number="bookForm.participants_count" type="number" min="1" label="Participants" />
          <AppTextField v-model.number="bookForm.stay_id" type="number" min="1" label="Stay ID (optional)" />
          <VSwitch v-model="bookForm.pay_now" label="Pay now (walk-in)" color="primary" hide-details />
          <AppSelect
            v-if="bookForm.pay_now"
            v-model="bookForm.payment_method"
            :items="[
              { title: 'Cash', value: 'cash' },
              { title: 'Card', value: 'card' },
            ]"
            item-title="title"
            item-value="value"
            label="Payment method"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="bookOpen = false">
            Cancel
          </VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!bookForm.guest_name" @click="submitBook">
            Book
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
