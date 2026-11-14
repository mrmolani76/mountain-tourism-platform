<script setup lang="ts">
useHead({ title: 'Stays' })

const client = useHotelApiClient()

interface FolioSnapshot {
  folio: Record<string, any>
  charges: Array<Record<string, any>>
  payments: Array<Record<string, any>>
  balance_cents: number
}

const rows = ref<Array<Stay>>([])
const loading = ref(false)
const filterStatus = ref<string | null>('in_house')
const configLoading = ref(true)
const listLoading = ref(false)
const tableConfig = ref<DataTableConfig | null>(null)
const listResult = ref<DataTableResult<any>>({ status: false, data: [], pagination: { total: 0 } })
const tableParams = ref<Record<string, any>>({})
const { get, post } = useApiClient()

const reservations = ref<Record<string, Reservation>>({})
const roomTitles = ref<Record<string, string>>({})
const bedTitles = ref<Record<string, string>>({})

const folioOpen = ref(false)
const folioLoading = ref(false)
const folioStay = ref<Stay | null>(null)
const folio = ref<FolioSnapshot | null>(null)

const statuses = [
  { value: 'in_house', title: 'In house' },
  { value: 'checked_out', title: 'Checked out' },
]

const headers = [
  { key: 'id', title: 'Stay', align: 'end' as const },
  { key: 'guest', title: 'Guest' },
  { key: 'accommodation', title: 'Accommodation', sortable: false },
  { key: 'checked_in_at', title: 'Checked in' },
  { key: 'checked_out_at', title: 'Checked out' },
  { key: 'status', title: 'Status' },
  { key: 'actions', title: '', sortable: false, align: 'end' as const },
]

const rowOf = (item: any): Stay => item?.raw ?? item

const reservationOf = (row: Stay) => row.reservation_id === null ? undefined : reservations.value[String(row.reservation_id)]

const guestName = (row: Stay) => reservationOf(row)?.guest_name ?? '--'

const accommodationLabel = (row: Stay) => {
  if (row.hut_bed_id !== null)
    return bedTitles.value[String(row.hut_bed_id)] ?? `Bed #${row.hut_bed_id}`

  if (row.room_id !== null)
    return roomTitles.value[String(row.room_id)] ?? `Room #${row.room_id}`

  return '--'
}

const formatDateTime = (value: string | null) => value ? String(value).replace('T', ' ').slice(0, 16) : '--'

const statusColor = (status: string) => status === 'in_house' ? 'success' : 'default'

const loadReference = async () => {
  const [reservationList, rooms, beds, dormitories] = await Promise.all([
    client.listReservations({ page: 1, page_size: 100 }),
    client.listRooms({ page: 1, page_size: 100 }),
    client.listBeds({ page: 1, page_size: 100 }),
    client.listDormitories({ page: 1, page_size: 100 }),
  ])

  reservations.value = Object.fromEntries(reservationList.data.map(row => [String(row.id), row]))

  roomTitles.value = Object.fromEntries(rooms.data.map(room => [
    String(room.id),
    `${room.room_number}${room.name ? ` ${room.name}` : ''}`,
  ]))

  const dormitoryTitles = Object.fromEntries(dormitories.data.map(row => [String(row.id), row.name]))

  bedTitles.value = Object.fromEntries(beds.data.map(bed => [
    String(bed.id),
    `${dormitoryTitles[String(bed.hut_dormitory_id)] ?? `Dormitory #${bed.hut_dormitory_id}`} / bed ${bed.bed_number}`,
  ]))
}

const loadConfig = async () => {
  configLoading.value = true
  try {
    const result = await get<ApiResponse<DataTableConfig>>('hotel/stays/list/config')
    if (result?.status)
      tableConfig.value = result.data as DataTableConfig
  }
  finally {
    configLoading.value = false
  }
}

const reloadListingData = async (params: any = tableParams.value) => {
  listLoading.value = true
  tableParams.value = params
  try {
    const body = {
      ...params,
      columnSearch: {
        ...(params?.columnSearch || {}),
        status: filterStatus.value || params?.columnSearch?.status,
      },
    }
    const result = await post<DataTableResult<any>>('hotel/stays/list', body)
    if (result?.status)
      listResult.value = result
  }
  finally {
    listLoading.value = false
  }
}

const load = async () => {
  loading.value = true
  await reloadListingData()
  loading.value = false
}

const openFolio = async (item: any) => {
  const id = Number(item.id ?? item.uuid)
  const row = await client.showStay(id) as Stay | null
  if (!row)
    return

  folioStay.value = row
  folio.value = null
  folioOpen.value = true
  folioLoading.value = true

  folio.value = await client.stayFolio(row.id)

  folioLoading.value = false
}

const checkOut = async (item: any) => {
  const id = Number(item.id ?? item.uuid)
  const outstanding = folioStay.value?.id === id && folio.value ? folio.value.balance_cents : null
  const note = outstanding !== null && outstanding !== 0
    ? ` Balance on folio is ${formatCents(outstanding)}.`
    : ''

  const confirmed = await showConfirmDialog(`Check out stay #${id}?${note}`, 'Check out', 'Cancel')

  if (!confirmed)
    return

  const response = await client.checkOut(id)

  if (response) {
    folioOpen.value = false
    await load()
  }
}

onMounted(async () => {
  await Promise.all([loadConfig(), loadReference()])
})
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <h2 class="text-h5 mb-1">
          Stays
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          In-house guests and their folio. Check out closes the folio.
        </p>
      </div>
      <VSelect
        v-model="filterStatus"
        :items="statuses"
        label="Status"
        density="compact"
        hide-details
        clearable
        style="min-inline-size: 200px;"
        @update:model-value="load"
      />
    </div>

    <template v-if="configLoading || !tableConfig">
      <VSkeletonLoader
        :loading="configLoading"
        type="table"
      />
    </template>
    <DataTable
      v-else
      v-bind="tableConfig"
      v-model="tableParams"
      :list-data="listResult.data"
      :total-items="listResult.pagination?.total ?? 0"
      :loading="listLoading || loading"
      :show-add-button="false"
      checkbox-id-key="id"
      @load-items="reloadListingData"
    >
      <template #actions="{ item }">
        <div class="d-flex gap-1">
          <VBtn
            size="38"
            color="primary"
            @click="navigateTo(`/hotel/stays/view/${item.id}`)"
          >
            <VTooltip activator="parent">
              View
            </VTooltip>
            <VIcon
              icon="tabler-eye"
              size="22"
            />
          </VBtn>
          <VBtn
            size="38"
            color="primary"
            @click="openFolio(item)"
          >
            <VTooltip activator="parent">
              Folio
            </VTooltip>
            <VIcon
              icon="tabler-receipt"
              size="22"
            />
          </VBtn>
          <VBtn
            v-if="(item.status?.label || item.status) === 'in_house'"
            size="38"
            color="error"
            variant="tonal"
            @click="checkOut(item)"
          >
            <VTooltip activator="parent">
              Check out
            </VTooltip>
            <VIcon
              icon="tabler-logout"
              size="22"
            />
          </VBtn>
        </div>
      </template>
    </DataTable>

    <VDialog v-model="folioOpen" max-width="860" scrollable>
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <span>Folio - {{ folioStay ? guestName(folioStay) : '' }}</span>
          <VChip
            v-if="folio"
            size="small"
            variant="tonal"
            :color="folio.folio.status === 'open' ? 'success' : 'default'"
          >
            {{ folio.folio.reference }} / {{ folio.folio.status }}
          </VChip>
        </VCardTitle>
        <VCardText>
          <div v-if="folioLoading" class="py-8 d-flex justify-center">
            <VProgressCircular indeterminate color="primary" />
          </div>

          <template v-else-if="folio">
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <div class="text-caption text-medium-emphasis">
                  Balance
                </div>
                <div class="text-h5">
                  {{ formatCents(folio.balance_cents) }}
                </div>
              </div>
              <div class="text-caption text-medium-emphasis text-end">
                Charges and payments are append-only.<br>
                Void with a reason, never edit.
              </div>
            </div>

            <div class="text-subtitle-2 mb-2">
              Charges
            </div>
            <VTable density="compact">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th class="text-end">Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="charge in folio.charges" :key="charge.id">
                  <td>{{ charge.posted_on }}</td>
                  <td>{{ charge.description }}</td>
                  <td class="text-end">
                    {{ formatCents(charge.amount_cents) }}
                  </td>
                  <td>
                    <VChip v-if="charge.is_voided" size="small" color="error" variant="tonal">
                      Voided
                    </VChip>
                    <span v-else class="text-medium-emphasis">Posted</span>
                  </td>
                </tr>
                <tr v-if="folio.charges.length === 0">
                  <td colspan="4" class="text-center text-medium-emphasis">
                    No charges yet.
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
                  <th>Date</th>
                  <th>Method</th>
                  <th>Reference</th>
                  <th class="text-end">Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="payment in folio.payments" :key="payment.id">
                  <td>{{ formatDateTime(payment.created_at) }}</td>
                  <td>{{ payment.method }}</td>
                  <td>{{ payment.reference ?? '--' }}</td>
                  <td class="text-end">
                    {{ formatCents(payment.amount_cents) }}
                  </td>
                  <td>
                    <VChip v-if="payment.is_voided" size="small" color="error" variant="tonal">
                      Voided
                    </VChip>
                    <span v-else class="text-medium-emphasis">Received</span>
                  </td>
                </tr>
                <tr v-if="folio.payments.length === 0">
                  <td colspan="5" class="text-center text-medium-emphasis">
                    No payments yet.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </template>

          <VAlert v-else type="info" variant="tonal" density="compact">
            This stay has no folio yet.
          </VAlert>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="folioOpen = false">
            Close
          </VBtn>
          <VBtn
            v-if="folio?.folio?.id"
            color="primary"
            variant="tonal"
            :to="`/folios/view/${folio.folio.id}`"
          >
            Open folio page
          </VBtn>
          <VBtn
            v-if="folioStay && folioStay.status === 'in_house'"
            color="error"
            variant="tonal"
            @click="checkOut(folioStay)"
          >
            Check out
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
