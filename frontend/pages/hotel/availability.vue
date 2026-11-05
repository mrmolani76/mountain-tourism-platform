<script setup lang="ts">
import type { AvailabilityBed, AvailabilityRoom, HotelOption } from '@/types/hotel'

useHead({ title: 'Availability' })

const client = useHotelApiClient()

const todayIso = () => new Date().toISOString().slice(0, 10)

const plusDaysIso = (days: number) => {
  const date = new Date()
  date.setDate(date.getDate() + days)

  return date.toISOString().slice(0, 10)
}

const checkInOn = ref(todayIso())
const checkOutOn = ref(plusDaysIso(1))
const buildingId = ref<number | null>(null)

const loading = ref(false)
const rooms = ref<Array<AvailabilityRoom>>([])
const beds = ref<Array<AvailabilityBed>>([])

const buildings = ref<Array<HotelOption>>([])
const roomTypeTitles = ref<Record<string, string>>({})
const dormitoryTitles = ref<Record<string, string>>({})

const roomHeaders = [
  { key: 'room_number', title: 'Room' },
  { key: 'name', title: 'Name' },
  { key: 'room_type_id', title: 'Room type' },
  { key: 'accommodation_type', title: 'Type' },
  { key: 'available', title: 'Availability' },
]

const bedHeaders = [
  { key: 'hut_dormitory_id', title: 'Dormitory' },
  { key: 'bed_number', title: 'Bed' },
  { key: 'available', title: 'Availability' },
]

const freeRoomCount = computed(() => rooms.value.filter(room => room.available).length)
const freeBedCount = computed(() => beds.value.filter(bed => bed.available).length)

const roomOf = (item: any) => item?.raw ?? item

const loadReference = async () => {
  const [buildingOptions, roomTypes, dormitories] = await Promise.all([
    client.listBuildingOptions(),
    client.listRoomTypes({ page: 1, page_size: 100 }),
    client.listDormitories({ page: 1, page_size: 100 }),
  ])

  buildings.value = buildingOptions
  roomTypeTitles.value = Object.fromEntries(roomTypes.data.map(row => [String(row.id), row.name]))
  dormitoryTitles.value = Object.fromEntries(dormitories.data.map(row => [String(row.id), row.name]))
}

const load = async () => {
  if (!checkInOn.value || !checkOutOn.value || checkOutOn.value <= checkInOn.value)
    return

  loading.value = true

  const data = await client.availability({
    check_in_on: checkInOn.value,
    check_out_on: checkOutOn.value,
    building_id: buildingId.value,
  })

  rooms.value = data.rooms
  beds.value = data.beds
  loading.value = false
}

onMounted(async () => {
  await Promise.all([loadReference(), load()])
})
</script>

<template>
  <div>
    <div class="mb-4">
      <h2 class="text-h5 mb-1">
        Availability
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Free rooms, chalets and hut beds for a date range. Overlap is checked on check in and check out dates.
      </p>
    </div>

    <VCard class="mb-4">
      <VCardText>
        <VRow dense align="end">
          <VCol cols="12" md="3">
            <VTextField v-model="checkInOn" type="date" label="Check in" density="compact" hide-details />
          </VCol>
          <VCol cols="12" md="3">
            <VTextField v-model="checkOutOn" type="date" label="Check out" density="compact" hide-details />
          </VCol>
          <VCol cols="12" md="3">
            <VSelect
              v-model="buildingId"
              :items="buildings"
              label="Building"
              density="compact"
              hide-details
              clearable
            />
          </VCol>
          <VCol cols="12" md="3">
            <VBtn block color="primary" prepend-icon="tabler-calendar-search" :loading="loading" @click="load">
              Check availability
            </VBtn>
          </VCol>
        </VRow>
        <VAlert
          v-if="checkInOn && checkOutOn && checkOutOn <= checkInOn"
          type="warning"
          variant="tonal"
          density="compact"
          class="mt-3"
        >
          Check out must be after check in.
        </VAlert>
      </VCardText>
    </VCard>

    <VRow>
      <VCol cols="12" lg="7">
        <VCard>
          <VCardItem>
            <VCardTitle>Rooms and chalets</VCardTitle>
            <VCardSubtitle>{{ freeRoomCount }} of {{ rooms.length }} free</VCardSubtitle>
          </VCardItem>
          <VDataTable
            :headers="roomHeaders"
            :items="rooms"
            :loading="loading"
            items-per-page="25"
            density="comfortable"
          >
            <template #item="{ item }">
              <tr>
                <td class="font-weight-medium">
                  {{ roomOf(item).room_number }}
                </td>
                <td>{{ roomOf(item).name ?? '--' }}</td>
                <td>{{ roomTypeTitles[String(roomOf(item).room_type_id)] ?? `Type #${roomOf(item).room_type_id}` }}</td>
                <td>{{ roomOf(item).accommodation_type }}</td>
                <td>
                  <VChip :color="roomOf(item).available ? 'success' : 'error'" size="small" variant="tonal">
                    {{ roomOf(item).available ? 'Free' : 'Taken' }}
                  </VChip>
                </td>
              </tr>
            </template>
            <template #no-data>
              <div class="py-6 text-center text-medium-emphasis">
                No rooms match this range.
              </div>
            </template>
          </VDataTable>
        </VCard>
      </VCol>

      <VCol cols="12" lg="5">
        <VCard>
          <VCardItem>
            <VCardTitle>Hut beds</VCardTitle>
            <VCardSubtitle>{{ freeBedCount }} of {{ beds.length }} free</VCardSubtitle>
          </VCardItem>
          <VDataTable
            :headers="bedHeaders"
            :items="beds"
            :loading="loading"
            items-per-page="25"
            density="comfortable"
          >
            <template #item="{ item }">
              <tr>
                <td>{{ dormitoryTitles[String(roomOf(item).hut_dormitory_id)] ?? `#${roomOf(item).hut_dormitory_id}` }}</td>
                <td class="font-weight-medium">
                  {{ roomOf(item).bed_number }}
                </td>
                <td>
                  <VChip :color="roomOf(item).available ? 'success' : 'error'" size="small" variant="tonal">
                    {{ roomOf(item).available ? 'Free' : 'Taken' }}
                  </VChip>
                </td>
              </tr>
            </template>
            <template #no-data>
              <div class="py-6 text-center text-medium-emphasis">
                No beds in this range.
              </div>
            </template>
          </VDataTable>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
