<script setup lang="ts">
import type { EquipmentType } from '@/composables/api-clients/useRentalApiClient'

definePageMeta({ navActiveLink: 'rental' })
useHead({ title: 'Equipment types' })

const client = useRentalApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<EquipmentType[]>([])
const dialogOpen = ref(false)
const form = ref({ name: '', description: '', is_active: true })

async function load() {
  loading.value = true
  const res = await client.listTypes()
  rows.value = res?.results ?? []
  loading.value = false
}

function openCreate() {
  form.value = { name: '', description: '', is_active: true }
  dialogOpen.value = true
}

async function save() {
  saving.value = true
  await client.createType({
    name: form.value.name,
    description: form.value.description || undefined,
    is_active: form.value.is_active,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Equipment type created.', 'OK')
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
          Equipment types
        </h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">
          Refresh
        </VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">
          Add type
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Active</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="3" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" />
            </td>
          </tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ row.name }}</td>
            <td>{{ row.description || '—' }}</td>
            <td>
              <VChip size="small" variant="tonal" :color="row.is_active ? 'success' : 'default'">
                {{ row.is_active ? 'Yes' : 'No' }}
              </VChip>
            </td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td colspan="3" class="text-center text-medium-emphasis py-8">
              No types yet.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="480">
      <VCard>
        <VCardTitle>Add equipment type</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppTextField v-model="form.name" label="Name" />
          <AppTextField v-model="form.description" label="Description" />
          <VSwitch v-model="form.is_active" label="Active" color="primary" hide-details />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">
            Cancel
          </VBtn>
          <VBtn color="primary" :loading="saving" :disabled="!form.name" @click="save">
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
