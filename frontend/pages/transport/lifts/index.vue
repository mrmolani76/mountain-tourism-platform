<script setup lang="ts">
import type { Lift } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Lifts' })

const client = useTransportApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<Lift[]>([])
const createOpen = ref(false)
const statusOpen = ref(false)
const activeLiftId = ref<number | null>(null)
const createForm = ref({ name: '', lift_type: 'chairlift', is_active: true })
const statusForm = ref({ status: 'closed', reason: '' })

async function load() {
  loading.value = true
  rows.value = (await client.listLifts())?.results ?? []
  loading.value = false
}

function openCreate() {
  createForm.value = { name: '', lift_type: 'chairlift', is_active: true }
  createOpen.value = true
}

async function saveCreate() {
  saving.value = true
  await client.createLift(createForm.value)
  saving.value = false
  createOpen.value = false
  await load()
}

function openStatus(id: number) {
  activeLiftId.value = id
  statusForm.value = { status: 'closed', reason: '' }
  statusOpen.value = true
}

async function saveStatus() {
  if (!activeLiftId.value)
    return
  saving.value = true
  await client.changeLiftStatus(activeLiftId.value, {
    status: statusForm.value.status,
    reason: statusForm.value.reason || undefined,
  })
  saving.value = false
  statusOpen.value = false
  showAlertDialog('Status updated', 'Lift status history appended.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
        <h2 class="text-h5 mb-1">Lifts</h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" :to="{ path: '/transport/lobby' }">Lobby board</VBtn>
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">Refresh</VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">Add lift</VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr><th>Name</th><th>Type</th><th>Status</th><th>Reason</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="text-center py-8"><VProgressCircular indeterminate color="primary" /></td></tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ row.name }}</td>
            <td>{{ row.lift_type }}</td>
            <td><VChip size="small" variant="tonal">{{ row.current_status || '—' }}</VChip></td>
            <td>{{ row.current_status_reason || '—' }}</td>
            <td class="text-end">
              <VBtn size="small" variant="tonal" color="warning" @click="openStatus(row.id)">Set status</VBtn>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length"><td colspan="5" class="text-center text-medium-emphasis py-8">No lifts yet.</td></tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="createOpen" max-width="480">
      <VCard>
        <VCardTitle>Add lift</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField v-model="createForm.name" label="Name" />
          <AppSelect
            v-model="createForm.lift_type"
            :items="[
              { title: 'Cable car', value: 'cable_car' },
              { title: 'Chairlift', value: 'chairlift' },
              { title: 'Drag lift', value: 'drag_lift' },
              { title: 'Gondola', value: 'gondola' },
              { title: 'Other', value: 'other' },
            ]"
            item-title="title" item-value="value" label="Type"
          />
          <VSwitch v-model="createForm.is_active" label="Active" color="primary" hide-details />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="createOpen = false">Cancel</VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!createForm.name" @click="saveCreate">Save</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog v-model="statusOpen" max-width="480">
      <VCard>
        <VCardTitle>Change lift status</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="statusForm.status"
            :items="[
              { title: 'Open', value: 'open' },
              { title: 'Closed', value: 'closed' },
              { title: 'Limited', value: 'limited' },
              { title: 'Weather hold', value: 'weather_hold' },
            ]"
            item-title="title" item-value="value" label="Status"
          />
          <AppTextField v-model="statusForm.reason" label="Reason" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="statusOpen = false">Cancel</VBtn>
          <VBtn color="primary" :loading="saving" @click="saveStatus">Update</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
