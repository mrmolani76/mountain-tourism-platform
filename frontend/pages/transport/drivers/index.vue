<script setup lang="ts">
import type { Driver } from '@/composables/api-clients/useTransportApiClient'

definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Drivers' })

const client = useTransportApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<Driver[]>([])
const dialogOpen = ref(false)
const form = ref({ name: '', phone: '', licence_no: '', is_active: true })

async function load() {
  loading.value = true
  rows.value = (await client.listDrivers())?.results ?? []
  loading.value = false
}

function openCreate() {
  form.value = { name: '', phone: '', licence_no: '', is_active: true }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  await client.createDriver({
    name: form.value.name,
    phone: form.value.phone || null,
    licence_no: form.value.licence_no || null,
    is_active: form.value.is_active,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Driver created. Drivers do not log in.', 'OK')
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
        <h2 class="text-h5 mb-1">Drivers</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">No driver login in MVP — passenger list print only.</p>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">Refresh</VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">Add driver</VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead><tr><th>Name</th><th>Phone</th><th>Licence</th><th>Active</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="4" class="text-center py-8"><VProgressCircular indeterminate color="primary" /></td></tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ row.name }}</td><td>{{ row.phone || '—' }}</td><td>{{ row.licence_no || '—' }}</td>
            <td>{{ row.is_active ? 'Yes' : 'No' }}</td>
          </tr>
          <tr v-if="!loading && !rows.length"><td colspan="4" class="text-center text-medium-emphasis py-8">No drivers yet.</td></tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="480">
      <VCard>
        <VCardTitle>Add driver</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField v-model="form.name" label="Name" />
          <AppTextField v-model="form.phone" label="Phone" />
          <AppTextField v-model="form.licence_no" label="Licence no" />
          <VSwitch v-model="form.is_active" label="Active" color="primary" hide-details />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">Cancel</VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!form.name" @click="save">Save</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
