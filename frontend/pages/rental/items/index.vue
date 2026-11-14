<script setup lang="ts">
import type { EquipmentItem, EquipmentType } from '@/composables/api-clients/useRentalApiClient'

definePageMeta({ navActiveLink: 'rental' })
useHead({ title: 'Equipment items' })

const client = useRentalApiClient()
const loading = ref(false)
const saving = ref(false)
const rows = ref<EquipmentItem[]>([])
const types = ref<EquipmentType[]>([])
const dialogOpen = ref(false)
const form = ref({
  equipment_type_id: null as number | null,
  item_code: '',
  size: '',
  condition: 'ok',
  is_active: true,
})

async function load() {
  loading.value = true
  const [itemRes, typeRes] = await Promise.all([
    client.listItems(),
    client.listTypes(),
  ])
  rows.value = itemRes?.results ?? []
  types.value = typeRes?.results ?? []
  loading.value = false
}

function typeName(id: number) {
  return types.value.find(t => t.id === id)?.name || String(id)
}

function openCreate() {
  form.value = {
    equipment_type_id: types.value[0]?.id ?? null,
    item_code: '',
    size: '',
    condition: 'ok',
    is_active: true,
  }
  dialogOpen.value = true
}

async function save() {
  if (!form.value.equipment_type_id)
    return
  saving.value = true
  await client.createItem({
    equipment_type_id: form.value.equipment_type_id,
    item_code: form.value.item_code,
    size: form.value.size || null,
    condition: form.value.condition,
    is_active: form.value.is_active,
  })
  saving.value = false
  dialogOpen.value = false
  showAlertDialog('Saved', 'Equipment item created.', 'OK')
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
          Equipment items
        </h2>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">
          Refresh
        </VBtn>
        <VBtn color="primary" prepend-icon="tabler-plus" @click="openCreate">
          Add item
        </VBtn>
      </div>
    </div>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Code</th>
            <th>Type</th>
            <th>Size</th>
            <th>Condition</th>
            <th>Active</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="5" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" />
            </td>
          </tr>
          <tr v-for="row in rows" v-else :key="row.id">
            <td>{{ row.item_code }}</td>
            <td>{{ typeName(row.equipment_type_id) }}</td>
            <td>{{ row.size || '—' }}</td>
            <td>{{ row.condition }}</td>
            <td>{{ row.is_active ? 'Yes' : 'No' }}</td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td colspan="5" class="text-center text-medium-emphasis py-8">
              No items yet.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="480">
      <VCard>
        <VCardTitle>Add item</VCardTitle>
        <VCardText class="d-flex flex-column gap-3">
          <AppSelect
            v-model="form.equipment_type_id"
            :items="types"
            item-title="name"
            item-value="id"
            label="Type"
          />
          <AppTextField v-model="form.item_code" label="Item code" />
          <AppTextField v-model="form.size" label="Size (optional)" />
          <AppSelect
            v-model="form.condition"
            :items="[
              { title: 'OK', value: 'ok' },
              { title: 'Damaged', value: 'damaged' },
              { title: 'Retired', value: 'retired' },
            ]"
            item-title="title"
            item-value="value"
            label="Condition"
          />
          <VSwitch v-model="form.is_active" label="Active" color="primary" hide-details />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!form.equipment_type_id || !form.item_code"
            @click="save"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
