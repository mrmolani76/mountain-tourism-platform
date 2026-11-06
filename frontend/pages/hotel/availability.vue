<script setup lang="ts">
/**
 * Availability check — EQMS DataTable configs from
 * GET hotel/availability/list/config; rows from GET hotel/availability (cell-shaped).
 */
useHead({ title: 'Availability' })

const client = useHotelApiClient()
const { get } = useApiClient()

const todayIso = () => new Date().toISOString().slice(0, 10)

const plusDaysIso = (days: number) => {
  const date = new Date()
  date.setDate(date.getDate() + days)

  return date.toISOString().slice(0, 10)
}

const checkInOn = ref(todayIso())
const checkOutOn = ref(plusDaysIso(1))
const buildingId = ref<number | null>(null)

const configLoading = ref(true)
const loading = ref(false)
const buildings = ref<Array<HotelOption>>([])

const roomTableConfig = ref<DataTableConfig | null>(null)
const bedTableConfig = ref<DataTableConfig | null>(null)
const roomListData = ref<Array<Record<string, any>>>([])
const bedListData = ref<Array<Record<string, any>>>([])
const freeRoomCount = ref(0)
const freeBedCount = ref(0)
const roomCount = ref(0)
const bedCount = ref(0)

const roomTableParams = ref<Record<string, any>>({})
const bedTableParams = ref<Record<string, any>>({})

const loadConfig = async () => {
  configLoading.value = true
  try {
    const result = await get<ApiResponse<{ rooms: DataTableConfig, beds: DataTableConfig }>>(
      'hotel/availability/list/config',
    )

    if (result?.status && result.data) {
      roomTableConfig.value = result.data.rooms
      bedTableConfig.value = result.data.beds
    }
  }
  finally {
    configLoading.value = false
  }
}

const load = async () => {
  if (!checkInOn.value || !checkOutOn.value || checkOutOn.value <= checkInOn.value)
    return

  loading.value = true

  const data = await client.availability({
    check_in_on: checkInOn.value,
    check_out_on: checkOutOn.value,
    building_id: buildingId.value,
  }) as AvailabilityListingPayload

  roomListData.value = data.rooms ?? []
  bedListData.value = data.beds ?? []
  freeRoomCount.value = data.free_room_count ?? 0
  freeBedCount.value = data.free_bed_count ?? 0
  roomCount.value = data.room_count ?? roomListData.value.length
  bedCount.value = data.bed_count ?? bedListData.value.length
  loading.value = false
}

onMounted(async () => {
  buildings.value = await client.listBuildingOptions()
  await Promise.all([loadConfig(), load()])
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

    <template v-if="configLoading || !roomTableConfig || !bedTableConfig">
      <VSkeletonLoader
        :loading="configLoading"
        type="table"
      />
    </template>

    <VRow v-else>
      <VCol
        cols="12"
        lg="7"
      >
        <VCard class="mb-2">
          <VCardItem>
            <VCardTitle>Rooms and chalets</VCardTitle>
            <VCardSubtitle>{{ freeRoomCount }} of {{ roomCount }} free</VCardSubtitle>
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
            <VCardSubtitle>{{ freeBedCount }} of {{ bedCount }} free</VCardSubtitle>
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
