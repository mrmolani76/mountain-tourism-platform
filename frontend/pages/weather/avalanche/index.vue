<script setup lang="ts">
import type { AvalancheDaily } from '@/composables/api-clients/useWeatherApiClient'

definePageMeta({ navActiveLink: 'weather' })
useHead({ title: 'Avalanche bulletin' })

const client = useWeatherApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<AvalancheDaily[]>([])
const dialogOpen = ref(false)
const today = new Date().toISOString().slice(0, 10)
const form = ref({
  valid_on: today,
  level: 2,
  source: '',
  notes: '',
})

async function load() {
  loading.value = true
  rows.value = (await client.listAvalanche())?.results ?? []
  loading.value = false
}

function openUpsert() {
  form.value = {
    valid_on: today,
    level: 2,
    source: '',
    notes: '',
  }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  await client.upsertAvalanche({
    valid_on: form.value.valid_on,
    level: form.value.level,
    source: form.value.source || undefined,
    notes: form.value.notes || undefined,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Avalanche bulletin upserted.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn
          variant="text"
          prepend-icon="tabler-arrow-left"
          :to="{ path: '/weather' }"
          class="mb-1 px-0"
        >
          Weather
        </VBtn>
        <h2 class="text-h5 mb-1">
          Avalanche bulletin
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Daily level stub (1–5). Owner/manager upsert.
        </p>
      </div>
      <div class="d-flex gap-2">
        <VBtn
          variant="tonal"
          prepend-icon="tabler-refresh"
          :loading="loading"
          @click="load"
        >
          Refresh
        </VBtn>
        <VBtn
          color="primary"
          prepend-icon="tabler-plus"
          @click="openUpsert"
        >
          Upsert day
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Valid on</th>
            <th>Level</th>
            <th>Source</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="4"
              class="text-center py-8"
            >
              <VProgressCircular
                indeterminate
                color="primary"
              />
            </td>
          </tr>
          <tr
            v-for="row in rows"
            v-else
            :key="row.id"
          >
            <td>{{ row.valid_on }}</td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="row.level >= 4 ? 'error' : row.level >= 3 ? 'warning' : undefined"
              >
                {{ row.level }}
              </VChip>
            </td>
            <td>{{ row.source || '—' }}</td>
            <td>{{ row.notes || '—' }}</td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td
              colspan="4"
              class="text-center text-medium-emphasis py-8"
            >
              No avalanche entries yet.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog
      v-model="dialogOpen"
      max-width="480"
    >
      <VCard>
        <VCardTitle>Upsert avalanche day</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField
            v-model="form.valid_on"
            type="date"
            label="Valid on"
          />
          <AppSelect
            v-model="form.level"
            :items="[1, 2, 3, 4, 5]"
            label="Level"
          />
          <AppTextField
            v-model="form.source"
            label="Source (optional)"
          />
          <AppTextField
            v-model="form.notes"
            label="Notes (optional)"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="dialogOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!form.valid_on"
            @click="save"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
