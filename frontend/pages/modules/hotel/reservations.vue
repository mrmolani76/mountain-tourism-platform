<script setup lang="ts">
import type { HotelOption, Reservation } from '@/types/hotel'
import { centsToMajor, formatCents, majorToCents } from '@/utils/money'

useHead({ title: 'Reservations' })

const client = useHotelApiClient()

interface AllocationDraft {
  accommodation_type: 'room' | 'bed'
  room_id: number | null
  hut_bed_id: number | null
}

const rows = ref<Array<Reservation>>([])
const loading = ref(false)
const saving = ref(false)
const dialogOpen = ref(false)
const editingId = ref<number | null>(null)

const cancelOpen = ref(false)
const cancelTarget = ref<Reservation | null>(null)
const cancelReason = ref('')
const cancelling = ref(false)

const filterGuest = ref('')
const filterStatus = ref<string | null>(null)
const filterFrom = ref('')
const filterTo = ref('')

const boardPlans = ref<Array<HotelOption>>([])
const roomOptions = ref<Array<HotelOption>>([])
const bedOptions = ref<Array<HotelOption>>([])

const statuses = [
  { value: 'pending', title: 'Pending' },
  { value: 'confirmed', title: 'Confirmed' },
  { value: 'checked_in', title: 'Checked in' },
  { value: 'checked_out', title: 'Checked out' },
  { value: 'cancelled', title: 'Cancelled' },
  { value: 'no_show', title: 'No show' },
]

const arrivalModes = [
  { value: 'car', title: 'Car' },
  { value: 'train', title: 'Train' },
  { value: 'plane', title: 'Plane' },
  { value: 'bus', title: 'Bus' },
]

const lockableStatuses = ['checked_in', 'checked_out', 'cancelled']

const headers = [
  { key: 'reference', title: 'Reference' },
  { key: 'guest_name', title: 'Guest' },
  { key: 'check_in_on', title: 'Check in' },
  { key: 'check_out_on', title: 'Check out' },
  { key: 'nights', title: 'Nights', align: 'end' as const },
  { key: 'people', title: 'People', align: 'end' as const, sortable: false },
  { key: 'accommodation', title: 'Accommodation', sortable: false },
  { key: 'status', title: 'Status' },
  { key: 'actions', title: '', sortable: false, align: 'end' as const },
]

const todayIso = () => new Date().toISOString().slice(0, 10)

const plusDaysIso = (days: number) => {
  const date = new Date()
  date.setDate(date.getDate() + days)

  return date.toISOString().slice(0, 10)
}

const blankAllocation = (): AllocationDraft => ({
  accommodation_type: 'room',
  room_id: null,
  hut_bed_id: null,
})

const blankForm = () => ({
  guest_name: '',
  guest_email: '',
  guest_phone: '',
  check_in_on: todayIso(),
  check_out_on: plusDaysIso(1),
  adults: 2,
  children: 0,
  board_plan_id: null as number | null,
  deposit: 0,
  arrival_mode: null as string | null,
  arrival_time: '',
  arrival_station: '',
  status: 'pending',
  notes: '',
  allocations: [blankAllocation()] as Array<AllocationDraft>,
})

const form = ref(blankForm())

const roomTitles = computed<Record<string, string>>(() => Object.fromEntries(
  roomOptions.value.map(option => [String(option.value), option.title]),
))

const bedTitles = computed<Record<string, string>>(() => Object.fromEntries(
  bedOptions.value.map(option => [String(option.value), option.title]),
))

const rowOf = (item: any): Reservation => item?.raw ?? item

const statusColor = (status: string) => {
  if (status === 'checked_in')
    return 'success'

  if (status === 'confirmed')
    return 'primary'

  if (status === 'pending')
    return 'warning'

  if (status === 'cancelled' || status === 'no_show')
    return 'error'

  return 'default'
}

const allocationLabel = (allocation: { accommodation_type: string, room_id: number | null, hut_bed_id: number | null }) => {
  if (allocation.accommodation_type === 'bed')
    return bedTitles.value[String(allocation.hut_bed_id)] ?? `Bed #${allocation.hut_bed_id}`

  return roomTitles.value[String(allocation.room_id)] ?? `Room #${allocation.room_id}`
}

const accommodationSummary = (row: Reservation) => {
  if (!row.allocations?.length)
    return '--'

  return row.allocations.map(allocationLabel).join(', ')
}

const nightsPreview = computed(() => {
  if (!form.value.check_in_on || !form.value.check_out_on)
    return 0

  const start = new Date(form.value.check_in_on).getTime()
  const end = new Date(form.value.check_out_on).getTime()

  return Math.max(0, Math.round((end - start) / 86400000))
})

const missing = computed(() => {
  const list: Array<string> = []

  if (!form.value.guest_name)
    list.push('Guest name')

  if (!form.value.check_in_on)
    list.push('Check in')

  if (!form.value.check_out_on)
    list.push('Check out')

  if (form.value.check_in_on && form.value.check_out_on && form.value.check_out_on <= form.value.check_in_on)
    list.push('Check out must be after check in')

  if (!form.value.adults || Number(form.value.adults) < 1)
    list.push('At least one adult')

  if (form.value.allocations.length === 0)
    list.push('At least one allocation')

  form.value.allocations.forEach((allocation, index) => {
    if (allocation.accommodation_type === 'room' && !allocation.room_id)
      list.push(`Room for allocation ${index + 1}`)

    if (allocation.accommodation_type === 'bed' && !allocation.hut_bed_id)
      list.push(`Bed for allocation ${index + 1}`)
  })

  return list
})

const loadOptions = async () => {
  const [plans, rooms, beds, dormitories] = await Promise.all([
    client.listBoardPlans({ page: 1, page_size: 100 }),
    client.listRooms({ page: 1, page_size: 100 }),
    client.listBeds({ page: 1, page_size: 100 }),
    client.listDormitories({ page: 1, page_size: 100 }),
  ])

  boardPlans.value = plans.data.map(plan => ({ value: plan.id, title: `${plan.name} (${formatCents(plan.price_per_person_cents)})` }))

  roomOptions.value = rooms.data.map(room => ({
    value: room.id,
    title: `${room.room_number}${room.name ? ` ${room.name}` : ''} (${room.accommodation_type})`,
  }))

  const dormitoryTitles = Object.fromEntries(dormitories.data.map(row => [String(row.id), row.name]))

  bedOptions.value = beds.data.map(bed => ({
    value: bed.id,
    title: `${dormitoryTitles[String(bed.hut_dormitory_id)] ?? `Dormitory #${bed.hut_dormitory_id}`} / bed ${bed.bed_number}`,
  }))
}

const load = async () => {
  loading.value = true

  const { data } = await client.listReservations({
    page: 1,
    page_size: 100,
    guest: filterGuest.value || undefined,
    status: filterStatus.value || undefined,
    from: filterFrom.value || undefined,
    to: filterTo.value || undefined,
  })

  rows.value = data
  loading.value = false
}

const resetFilters = async () => {
  filterGuest.value = ''
  filterStatus.value = null
  filterFrom.value = ''
  filterTo.value = ''

  await load()
}

const openCreate = () => {
  editingId.value = null
  form.value = blankForm()
  dialogOpen.value = true
}

const openEdit = (row: Reservation) => {
  editingId.value = row.id
  form.value = {
    guest_name: row.guest_name,
    guest_email: row.guest_email ?? '',
    guest_phone: row.guest_phone ?? '',
    check_in_on: row.check_in_on,
    check_out_on: row.check_out_on,
    adults: row.adults,
    children: row.children,
    board_plan_id: row.board_plan_id,
    deposit: centsToMajor(row.deposit_cents),
    arrival_mode: row.arrival_mode,
    arrival_time: row.arrival_time ? String(row.arrival_time).slice(0, 5) : '',
    arrival_station: row.arrival_station ?? '',
    status: row.status === 'confirmed' ? 'confirmed' : 'pending',
    notes: row.notes ?? '',
    allocations: row.allocations?.length
      ? row.allocations.map(allocation => ({
        accommodation_type: allocation.accommodation_type === 'bed' ? 'bed' : 'room',
        room_id: allocation.room_id,
        hut_bed_id: allocation.hut_bed_id,
      }) as AllocationDraft)
      : [blankAllocation()],
  }

  dialogOpen.value = true
}

const addAllocation = () => {
  form.value.allocations.push(blankAllocation())
}

const removeAllocation = (index: number) => {
  if (form.value.allocations.length === 1)
    return

  form.value.allocations.splice(index, 1)
}

const onAllocationTypeChange = (allocation: AllocationDraft) => {
  allocation.room_id = null
  allocation.hut_bed_id = null
}

const buildPayload = () => ({
  guest_name: form.value.guest_name,
  guest_email: form.value.guest_email || null,
  guest_phone: form.value.guest_phone || null,
  check_in_on: form.value.check_in_on,
  check_out_on: form.value.check_out_on,
  adults: Number(form.value.adults),
  children: Number(form.value.children ?? 0),
  board_plan_id: form.value.board_plan_id ?? null,
  deposit_cents: majorToCents(form.value.deposit),
  arrival_mode: form.value.arrival_mode || null,
  arrival_time: form.value.arrival_time ? form.value.arrival_time.slice(0, 5) : null,
  arrival_station: form.value.arrival_station || null,
  status: form.value.status === 'confirmed' ? 'confirmed' : 'pending',
  notes: form.value.notes || null,
  allocations: form.value.allocations.map(allocation => allocation.accommodation_type === 'bed'
    ? { accommodation_type: 'bed', hut_bed_id: Number(allocation.hut_bed_id) }
    : { accommodation_type: 'room', room_id: Number(allocation.room_id) }),
})

const save = async () => {
  if (missing.value.length > 0)
    return

  saving.value = true

  const payload = buildPayload()
  const response = editingId.value === null
    ? await client.createReservation(payload)
    : await client.updateReservation(editingId.value, payload)

  saving.value = false

  if (response) {
    dialogOpen.value = false
    await load()
  }
}

const canOperate = (row: Reservation) => !lockableStatuses.includes(row.status)

const openCancel = (row: Reservation) => {
  cancelTarget.value = row
  cancelReason.value = ''
  cancelOpen.value = true
}

const submitCancel = async () => {
  if (!cancelTarget.value || !cancelReason.value.trim())
    return

  cancelling.value = true
  const response = await client.cancelReservation(cancelTarget.value.id, cancelReason.value.trim())
  cancelling.value = false

  if (response) {
    cancelOpen.value = false
    await load()
  }
}

const checkIn = async (row: Reservation) => {
  const confirmed = await showConfirmDialog(`Check in ${row.guest_name}?`, 'Check in', 'Cancel')

  if (!confirmed)
    return

  const response = await client.checkIn(row.id)

  if (response) {
    await load()
    showAlertDialog('Checked in', `Stay created. ${response.folio ? `Folio ${(response.folio as any).reference}.` : 'No folio opened.'}`, 'OK')
  }
}

onMounted(async () => {
  await Promise.all([load(), loadOptions()])
})
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <h2 class="text-h5 mb-1">
          Reservations
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Bookings hold rooms or hut beds for a date range. Check in opens the stay and folio.
        </p>
      </div>
      <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">
        New reservation
      </VBtn>
    </div>

    <VCard class="mb-4">
      <VCardText>
        <VRow dense>
          <VCol cols="12" md="3">
            <VTextField
              v-model="filterGuest"
              label="Guest"
              density="compact"
              hide-details
              clearable
              prepend-inner-icon="tabler-user-search"
              @keyup.enter="load"
              @click:clear="load"
            />
          </VCol>
          <VCol cols="12" md="2">
            <VSelect
              v-model="filterStatus"
              :items="statuses"
              label="Status"
              density="compact"
              hide-details
              clearable
              @update:model-value="load"
            />
          </VCol>
          <VCol cols="12" md="2">
            <VTextField
              v-model="filterFrom"
              type="date"
              label="From"
              density="compact"
              hide-details
              @change="load"
            />
          </VCol>
          <VCol cols="12" md="2">
            <VTextField
              v-model="filterTo"
              type="date"
              label="To"
              density="compact"
              hide-details
              @change="load"
            />
          </VCol>
          <VCol cols="12" md="3" class="d-flex gap-2">
            <VBtn variant="tonal" prepend-icon="tabler-search" height="40" @click="load">
              Apply
            </VBtn>
            <VBtn variant="text" @click="resetFilters">
              Reset
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard>
      <VDataTable
        :headers="headers"
        :items="rows"
        :loading="loading"
        items-per-page="25"
        density="comfortable"
      >
        <template #item="{ item }">
          <tr>
            <td class="font-weight-medium">
              {{ rowOf(item).reference }}
            </td>
            <td>{{ rowOf(item).guest_name }}</td>
            <td>{{ rowOf(item).check_in_on }}</td>
            <td>{{ rowOf(item).check_out_on }}</td>
            <td class="text-end">
              {{ rowOf(item).nights }}
            </td>
            <td class="text-end">
              {{ rowOf(item).adults }} + {{ rowOf(item).children }}
            </td>
            <td>{{ accommodationSummary(rowOf(item)) }}</td>
            <td>
              <VChip :color="statusColor(rowOf(item).status)" size="small" variant="tonal">
                {{ rowOf(item).status }}
              </VChip>
            </td>
            <td class="text-end">
              <VBtn
                v-if="canOperate(rowOf(item))"
                icon="tabler-edit"
                size="small"
                variant="text"
                color="primary"
                @click="openEdit(rowOf(item))"
              />
              <VBtn
                v-if="canOperate(rowOf(item))"
                icon="tabler-login"
                size="small"
                variant="text"
                color="success"
                @click="checkIn(rowOf(item))"
              />
              <VBtn
                v-if="canOperate(rowOf(item))"
                icon="tabler-ban"
                size="small"
                variant="text"
                color="error"
                @click="openCancel(rowOf(item))"
              />
            </td>
          </tr>
        </template>

        <template #no-data>
          <div class="py-8 text-center text-medium-emphasis">
            No reservations for these filters.
          </div>
        </template>
      </VDataTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="900" persistent scrollable>
      <VCard>
        <VCardTitle>{{ editingId === null ? 'New reservation' : 'Edit reservation' }}</VCardTitle>
        <VCardText>
          <div class="text-subtitle-2 mb-2">
            Guest
          </div>
          <VRow dense>
            <VCol cols="12" md="4">
              <VTextField v-model="form.guest_name" label="Guest name" density="comfortable" variant="outlined" required />
            </VCol>
            <VCol cols="12" md="4">
              <VTextField v-model="form.guest_email" label="Email" type="email" density="comfortable" variant="outlined" />
            </VCol>
            <VCol cols="12" md="4">
              <VTextField v-model="form.guest_phone" label="Phone" density="comfortable" variant="outlined" />
            </VCol>
          </VRow>

          <div class="text-subtitle-2 mt-4 mb-2">
            Stay
          </div>
          <VRow dense>
            <VCol cols="6" md="3">
              <VTextField v-model="form.check_in_on" type="date" label="Check in" density="comfortable" variant="outlined" required />
            </VCol>
            <VCol cols="6" md="3">
              <VTextField v-model="form.check_out_on" type="date" label="Check out" density="comfortable" variant="outlined" required />
            </VCol>
            <VCol cols="6" md="2">
              <VTextField v-model.number="form.adults" type="number" min="1" label="Adults" density="comfortable" variant="outlined" required />
            </VCol>
            <VCol cols="6" md="2">
              <VTextField v-model.number="form.children" type="number" min="0" label="Children" density="comfortable" variant="outlined" />
            </VCol>
            <VCol cols="12" md="2">
              <VTextField :model-value="nightsPreview" label="Nights" density="comfortable" variant="outlined" readonly />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect
                v-model="form.board_plan_id"
                :items="boardPlans"
                label="Board plan"
                density="comfortable"
                variant="outlined"
                clearable
              />
            </VCol>
            <VCol cols="12" md="3">
              <VTextField
                v-model.number="form.deposit"
                type="number"
                step="0.01"
                min="0"
                label="Deposit (EUR)"
                density="comfortable"
                variant="outlined"
              />
            </VCol>
            <VCol cols="12" md="3">
              <VSelect
                :model-value="form.status"
                :items="statuses.slice(0, 2)"
                label="Status"
                density="comfortable"
                variant="outlined"
                @update:model-value="form.status = String($event)"
              />
            </VCol>
          </VRow>

          <div class="text-subtitle-2 mt-4 mb-2">
            Arrival
          </div>
          <VRow dense>
            <VCol cols="6" md="3">
              <VSelect
                v-model="form.arrival_mode"
                :items="arrivalModes"
                label="Mode"
                density="comfortable"
                variant="outlined"
                clearable
              />
            </VCol>
            <VCol cols="6" md="3">
              <VTextField v-model="form.arrival_time" type="time" label="Time" density="comfortable" variant="outlined" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.arrival_station" label="Station or pickup point" density="comfortable" variant="outlined" />
            </VCol>
          </VRow>

          <div class="text-subtitle-2 mt-4 mb-2">
            Rooms and beds
          </div>
          <VCard
            v-for="(allocation, index) in form.allocations"
            :key="index"
            variant="outlined"
            class="mb-2"
          >
            <VCardText class="py-3">
              <VRow dense align="center">
                <VCol cols="12" md="3">
                  <VSelect
                    v-model="allocation.accommodation_type"
                    :items="[{ value: 'room', title: 'Room or chalet' }, { value: 'bed', title: 'Hut bed' }]"
                    label="Type"
                    density="compact"
                    variant="outlined"
                    hide-details
                    @update:model-value="onAllocationTypeChange(allocation)"
                  />
                </VCol>
                <VCol cols="12" md="7">
                  <VSelect
                    v-if="allocation.accommodation_type === 'room'"
                    v-model="allocation.room_id"
                    :items="roomOptions"
                    label="Room or chalet"
                    density="compact"
                    variant="outlined"
                    hide-details
                  />
                  <VSelect
                    v-else
                    v-model="allocation.hut_bed_id"
                    :items="bedOptions"
                    label="Hut bed"
                    density="compact"
                    variant="outlined"
                    hide-details
                  />
                </VCol>
                <VCol cols="12" md="2" class="d-flex justify-end">
                  <VBtn
                    icon="tabler-trash"
                    size="small"
                    variant="text"
                    color="error"
                    :disabled="form.allocations.length === 1"
                    @click="removeAllocation(index)"
                  />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
          <VBtn variant="tonal" size="small" prepend-icon="tabler-plus" @click="addAllocation">
            Add allocation
          </VBtn>

          <div class="text-subtitle-2 mt-4 mb-2">
            Notes
          </div>
          <VTextarea v-model="form.notes" label="Notes" rows="2" density="comfortable" variant="outlined" auto-grow />

          <VAlert
            v-if="missing.length > 0"
            type="warning"
            variant="tonal"
            density="compact"
            class="mt-4"
          >
            Required: {{ missing.join(', ') }}
          </VAlert>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">
            Cancel
          </VBtn>
          <VBtn color="primary" :loading="saving" :disabled="missing.length > 0" @click="save">
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog v-model="cancelOpen" max-width="480" persistent>
      <VCard>
        <VCardTitle>Cancel reservation</VCardTitle>
        <VCardText>
          <p class="text-body-2 text-medium-emphasis">
            {{ cancelTarget?.reference }} - {{ cancelTarget?.guest_name }}
          </p>
          <VTextarea
            v-model="cancelReason"
            label="Reason"
            rows="3"
            density="comfortable"
            variant="outlined"
            hint="Cancellations keep the record. The reason is stored with the reservation."
            persistent-hint
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="cancelOpen = false">
            Keep reservation
          </VBtn>
          <VBtn
            color="error"
            :loading="cancelling"
            :disabled="!cancelReason.trim()"
            @click="submitCancel"
          >
            Cancel reservation
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
