<script setup lang="ts">
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

const roomColumns: Array<HotelColumn> = [
  { key: 'room_number', title: 'Room' },
  { key: 'name', title: 'Name' },
  { key: 'room_type', title: 'Room type' },
  { key: 'accommodation_type', title: 'Type' },
  { key: 'availability', title: 'Availability', kind: 'status' },
]

const bedColumns: Array<HotelColumn> = [
  { key: 'dormitory', title: 'Dormitory' },
  { key: 'bed_number', title: 'Bed' },
  { key: 'availability', title: 'Availability', kind: 'status' },
]

const roomTableConfig = computed(() => buildDataTableConfig(roomColumns, {
  defaultSortKey: 'room_number',
  includeActions: false,
}))
const bedTableConfig = computed(() => buildDataTableConfig(bedColumns, {
  defaultSortKey: 'bed_number',
  includeActions: false,
}))

const roomTableParams = ref<Record<string, any>>({})
const bedTableParams = ref<Record<string, any>>({})

const freeRoomCount = computed(() => rooms.value.filter(room => room.available).length)
const freeBedCount = computed(() => beds.value.filter(bed => bed.available).length)

const roomListData = computed(() => rooms.value.map((room) => {
  const mapped = mapRowToDataTableCells({
    id: room.id,
    room_number: room.room_number,
    name: room.name,
    room_type: roomTypeTitles.value[String(room.room_type_id)] ?? `Type #${room.room_type_id}`,
    accommodation_type: room.accommodation_type,
    availability: room.available ? 'Free' : 'Taken',
  }, roomColumns)

  mapped.availability = {
    label: room.available ? 'Free' : 'Taken',
    type: 'status',
    color: room.available ? 'success' : 'error',
  }

  return mapped
}))

const bedListData = computed(() => beds.value.map((bed) => {
  const mapped = mapRowToDataTableCells({
    id: bed.id,
    dormitory: dormitoryTitles.value[String(bed.hut_dormitory_id)] ?? `#${bed.hut_dormitory_id}`,
    bed_number: bed.bed_number,
    availability: bed.available ? 'Free' : 'Taken',
  }, bedColumns)

  mapped.availability = {
    label: bed.available ? 'Free' : 'Taken',
    type: 'status',
    color: bed.available ? 'success' : 'error',
  }

  return mapped
}))

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
        <VRow
          dense
          align="end"
        >
          <VCol
            cols="12"
            md="3"
          >
            <VTextField
              v-model="checkInOn"
              type="date"
              label="Check in"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VTextField
              v-model="checkOutOn"
              type="date"
              label="Check out"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="buildingId"
              :items="buildings"
              label="Building"
              density="compact"
              hide-details
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VBtn
              block
              color="primary"
              prepend-icon="tabler-calendar-search"
              :loading="loading"
              @click="load"
            >
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
      <VCol
        cols="12"
        lg="7"
      >
        <VCard class="mb-2">
          <VCardItem>
            <VCardTitle>Rooms and chalets</VCardTitle>
            <VCardSubtitle>{{ freeRoomCount }} of {{ rooms.length }} free</VCardSubtitle>
          </VCardItem>
        </VCard>
        <DataTable
          v-bind="roomTableConfig"
          v-model="roomTableParams"
          :list-data="roomListData"
          :total-items="roomListData.length"
          :loading="loading"
          :show-add-button="false"
          :show-column-field="false"
          checkbox-id-key="id"
          @load-items="() => undefined"
        />
      </VCol>

      <VCol
        cols="12"
        lg="5"
      >
        <VCard class="mb-2">
          <VCardItem>
            <VCardTitle>Hut beds</VCardTitle>
            <VCardSubtitle>{{ freeBedCount }} of {{ beds.length }} free</VCardSubtitle>
          </VCardItem>
        </VCard>
        <DataTable
          v-bind="bedTableConfig"
          v-model="bedTableParams"
          :list-data="bedListData"
          :total-items="bedListData.length"
          :loading="loading"
          :show-add-button="false"
          :show-column-field="false"
          checkbox-id-key="id"
          @load-items="() => undefined"
        />
      </VCol>
    </VRow>
  </div>
</template>
