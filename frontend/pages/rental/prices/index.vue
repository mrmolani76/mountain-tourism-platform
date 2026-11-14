<script setup lang="ts">
import type { EquipmentType } from '@/composables/api-clients/useRentalApiClient'

definePageMeta({ navActiveLink: 'rental' })
useHead({ title: 'Rental season prices' })

const client = useRentalApiClient()
const { get } = useApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<Array<Record<string, any>>>([])
const types = ref<EquipmentType[]>([])
const seasons = ref<Array<{ id: number; name: string }>>([])
const dialogOpen = ref(false)
const form = ref({
  equipment_type_id: null as number | null,
  season_id: null as number | null,
  day_rate: 0,
})

async function loadSeasons() {
  const res = await get<{ results?: Array<Record<string, any>>; data?: Array<Record<string, any>> } | Array<Record<string, any>>>('seasons')
  const list = Array.isArray(res) ? res : (res?.results ?? res?.data ?? [])
  seasons.value = list.map((s: any) => ({ id: s.id, name: s.name || s.label || `Season ${s.id}` }))
}

async function load() {
  loading.value = true
  const [priceRes, typeRes] = await Promise.all([
    client.listSeasonPrices(),
    client.listTypes(),
  ])
  rows.value = priceRes?.results ?? []
  types.value = typeRes?.results ?? []
  await loadSeasons()
  loading.value = false
}

function typeName(id: number) {
  return types.value.find(t => t.id === id)?.name || String(id)
}

function seasonName(id: number) {
  return seasons.value.find(s => s.id === id)?.name || String(id)
}

function openCreate() {
  form.value = {
    equipment_type_id: types.value[0]?.id ?? null,
    season_id: seasons.value[0]?.id ?? null,
    day_rate: 0,
  }
  dialogOpen.value = true
}

async function save() {
  if (!form.value.equipment_type_id || !form.value.season_id)
    return
  saving.value = true
  await client.createSeasonPrice({
    equipment_type_id: form.value.equipment_type_id,
    season_id: form.value.season_id,
    day_rate_cents: majorToCents(form.value.day_rate),
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Season day rate stored.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/rental' }" class="mb-1 px-0">
          Rental
        </VBtn>
        <h2 class="text-h5 mb-1">
          Season prices
        </h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">
          Refresh
        </VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">
          Add rate
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Type</th>
            <th>Season</th>
            <th class="text-end">
              Day rate
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="3" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" />
            </td>
          </tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ typeName(row.equipment_type_id) }}</td>
            <td>{{ seasonName(row.season_id) }}</td>
            <td class="text-end">
              {{ formatCents(row.day_rate_cents) }}
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td colspan="3" class="text-center text-medium-emphasis py-8">
              No season prices yet.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="480">
      <VCard>
        <VCardTitle>Add season price</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect v-model="form.equipment_type_id" :items="types" item-title="name" item-value="id" label="Type" />
          <AppSelect v-model="form.season_id" :items="seasons" item-title="name" item-value="id" label="Season" />
          <AppTextField v-model.number="form.day_rate" type="number" step="0.01" min="0" label="Day rate (EUR)" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!form.equipment_type_id || !form.season_id"
            @click="save"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
