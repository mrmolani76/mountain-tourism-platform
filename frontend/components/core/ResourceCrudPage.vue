<script setup lang="ts">
import type { HotelColumn, HotelFormField, HotelOption } from '@/types/hotel'
import { formatCents, centsToMajor, majorToCents } from '@/utils/money'

/**
 * Generic CRUD page for Core (staff, buildings, outlets, seasons) and other
 * envelope list APIs. Field list drives the dialog form.
 */

const props = withDefaults(defineProps<{
  title: string
  subtitle?: string
  endpoint: string
  columns: Array<HotelColumn>
  fields: Array<HotelFormField>
  defaults?: Record<string, any>
  canCreate?: boolean
  canDelete?: boolean
  canEdit?: boolean
  listParams?: Record<string, unknown>
  addLabel?: string
}>(), {
  subtitle: '',
  defaults: () => ({}),
  canCreate: true,
  canDelete: true,
  canEdit: true,
  listParams: () => ({}),
  addLabel: 'Add',
})

const { t } = useI18n()
const client = useResourceApiClient()

const rows = ref<Array<Record<string, any>>>([])
/** First paint skeleton. */
const initialLoading = ref(true)
/** Table overlay while list refreshes. */
const loading = ref(false)
const search = ref('')
const dialogOpen = ref(false)
const saving = ref(false)
const editingId = ref<number | null>(null)
const form = ref<Record<string, any>>({})
const resolvedOptions = ref<Record<string, Array<HotelOption>>>({})

const headers = computed(() => [
  ...props.columns,
  { key: 'actions', title: '', sortable: false, align: 'end' as const },
])

const dialogTitle = computed(() => editingId.value === null ? props.addLabel : `Edit ${props.title.replace(/s$/, '')}`)

const blankForm = () => {
  const base: Record<string, any> = {
    is_active: true,
    ...props.defaults,
  }

  props.fields.forEach((field) => {
    if (field.type === 'checkbox' && base[field.key] === undefined)
      base[field.key] = false

    if (field.type === 'multiselect' && base[field.key] === undefined)
      base[field.key] = []

    if (base[field.key] === undefined)
      base[field.key] = null
  })

  return base
}

const load = async () => {
  loading.value = true
  try {
    const { data } = await client.list(props.endpoint, { page: 1, page_size: 100, ...props.listParams })

    rows.value = (data as Array<Record<string, any>>) ?? []
  }
  finally {
    loading.value = false
    initialLoading.value = false
  }
}

const loadOptions = async () => {
  const optionMap: Record<string, Array<HotelOption>> = {}

  await Promise.all(props.fields.map(async (field) => {
    if (field.optionsLoader)
      optionMap[field.key] = await field.optionsLoader()
    else if (field.options)
      optionMap[field.key] = field.options
  }))

  resolvedOptions.value = optionMap
}

const openCreate = () => {
  editingId.value = null
  form.value = blankForm()
  dialogOpen.value = true
}

const openEdit = (row: Record<string, any>) => {
  editingId.value = Number(row.id)

  const values: Record<string, any> = { ...row }

  props.fields.forEach((field) => {
    if (field.type === 'money')
      values[field.key] = centsToMajor(row[field.key])

    if (field.type === 'multiselect' && !Array.isArray(values[field.key]))
      values[field.key] = []

    // Never echo hashed passwords into the edit form.
    if (field.type === 'password')
      values[field.key] = ''
  })

  form.value = values
  dialogOpen.value = true
}

const missingRequired = computed(() => props.fields
  .filter((field) => {
    if (!field.required)
      return false

    // Password required on create only.
    if (field.type === 'password' && editingId.value !== null)
      return false

    return true
  })
  .filter((field) => {
    const value = form.value[field.key]

    return value === null || value === undefined || value === ''
  })
  .map(field => field.label))

const buildPayload = () => {
  const payload: Record<string, any> = {}

  props.fields.forEach((field) => {
    const value = form.value[field.key]

    if (field.type === 'money') {
      payload[field.key] = majorToCents(value)

      return
    }

    // Omit blank password on edit so the API keeps the existing hash.
    if (field.type === 'password' && (value === null || value === undefined || value === ''))
      return

    payload[field.key] = value
  })

  return payload
}

const save = async () => {
  if (missingRequired.value.length > 0)
    return

  saving.value = true

  const payload = buildPayload()
  const response = editingId.value === null
    ? await client.create(props.endpoint, payload)
    : await client.update(props.endpoint, editingId.value, payload)

  saving.value = false

  if (response) {
    dialogOpen.value = false
    await load()
  }
}

const destroy = async (row: Record<string, any>) => {
  const confirmed = await showConfirmDialog(`Delete "${row.name ?? row.room_number ?? row.bed_number ?? row.id}"?`, t('delete'), t('cancel'))

  if (!confirmed)
    return

  await client.remove(props.endpoint, Number(row.id))
  await load()
}

const cellText = (row: Record<string, any>, column: HotelColumn) => {
  const value = row[column.key]

  if (value === null || value === undefined || value === '')
    return '--'

  if (column.kind === 'money')
    return formatCents(value)

  if (column.kind === 'bool')
    return value ? 'Active' : 'Inactive'

  if (column.kind === 'date')
    return String(value).slice(0, 10)

  if (column.kind === 'datetime')
    return String(value).replace('T', ' ').slice(0, 16)

  return String(value)
}

/** Vuetify 3.10 wraps rows as `{ raw, columns }`; older shapes pass the row directly. */
const rawItem = (item: any): Record<string, any> => item?.raw ?? item

const optionLabel = (field: HotelFormField, value: any) => {
  const options = resolvedOptions.value[field.key] ?? field.options ?? []
  const match = options.find(option => String(option.value) === String(value))

  return match?.title ?? value ?? '--'
}

const displayValue = (row: Record<string, any>, column: HotelColumn) => {
  const field = props.fields.find(item => item.key === column.key)

  if (field && (field.type === 'select' || field.type === 'multiselect'))
    return optionLabel(field, row[column.key])

  return cellText(row, column)
}

onMounted(async () => {
  await Promise.all([load(), loadOptions()])
})
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <h2 class="text-h5 mb-1">
          {{ title }}
        </h2>
        <p v-if="subtitle" class="text-body-2 text-medium-emphasis mb-0">
          {{ subtitle }}
        </p>
      </div>
      <div
        v-if="!initialLoading"
        class="d-flex align-center gap-2"
      >
        <VTextField
          v-model="search"
          density="compact"
          hide-details
          placeholder="Search"
          prepend-inner-icon="tabler-search"
          style="min-inline-size: 220px;"
        />
        <VBtn
          v-if="canCreate"
          color="primary"
          prepend-icon="tabler-plus"
          @click="openCreate"
        >
          {{ addLabel }}
        </VBtn>
      </div>
    </div>

    <template v-if="initialLoading">
      <VSkeletonLoader
        :loading="initialLoading"
        type="table"
      />
    </template>

    <VCard v-else>
      <VDataTable
        v-model:search="search"
        :headers="headers"
        :items="rows"
        :loading="loading"
        items-per-page="25"
        density="comfortable"
      >
        <template #item="{ item }">
          <tr>
            <td v-for="column in columns" :key="column.key" :class="column.align === 'end' ? 'text-end' : ''">
              <VChip
                v-if="column.kind === 'bool'"
                :color="rawItem(item)[column.key] ? 'success' : 'default'"
                size="small"
                variant="tonal"
              >
                {{ rawItem(item)[column.key] ? 'Active' : 'Inactive' }}
              </VChip>
              <VChip
                v-else-if="column.kind === 'status'"
                size="small"
                variant="tonal"
                :color="rawItem(item)[column.key] === 'checked_in' ? 'success' : rawItem(item)[column.key] === 'cancelled' ? 'error' : 'primary'"
              >
                {{ rawItem(item)[column.key] }}
              </VChip>
              <span v-else>{{ displayValue(rawItem(item), column) }}</span>
            </td>
            <td class="text-end">
              <VBtn
                v-if="canEdit"
                icon="tabler-edit"
                size="small"
                variant="text"
                color="primary"
                @click="openEdit(rawItem(item))"
              />
              <VBtn
                v-if="canDelete"
                icon="tabler-trash"
                size="small"
                variant="text"
                color="error"
                @click="destroy(rawItem(item))"
              />
            </td>
          </tr>
        </template>

        <template #no-data>
          <div class="py-8 text-center text-medium-emphasis">
            No records yet.
          </div>
        </template>
      </VDataTable>
    </VCard>

    <VDialog v-model="dialogOpen" max-width="640" persistent>
      <VCard>
        <VCardTitle>{{ dialogTitle }}</VCardTitle>
        <VCardText>
          <VRow>
            <VCol
              v-for="field in fields"
              :key="field.key"
              :cols="12"
              :md="field.col ?? 6"
            >
              <VCheckbox
                v-if="field.type === 'checkbox'"
                v-model="form[field.key]"
                :label="field.label"
                hide-details
              />

              <VSelect
                v-else-if="field.type === 'select'"
                v-model="form[field.key]"
                :items="resolvedOptions[field.key] ?? field.options ?? []"
                :label="field.label"
                :hint="field.hint"
                :required="field.required"
                clearable
                density="comfortable"
                persistent-hint
                variant="outlined"
              />

              <VSelect
                v-else-if="field.type === 'multiselect'"
                v-model="form[field.key]"
                :items="resolvedOptions[field.key] ?? field.options ?? []"
                :label="field.label"
                :hint="field.hint"
                chips
                closable-chips
                density="comfortable"
                multiple
                persistent-hint
                variant="outlined"
              />

              <VTextarea
                v-else-if="field.type === 'textarea'"
                v-model="form[field.key]"
                :label="field.label"
                :hint="field.hint"
                density="comfortable"
                persistent-hint
                rows="2"
                variant="outlined"
              />

              <VTextField
                v-else
                v-model="form[field.key]"
                :type="field.type === 'money' || field.type === 'number' ? 'number' : (field.type === 'password' ? 'password' : (field.type ?? 'text'))"
                :label="field.label"
                :hint="field.hint"
                :min="field.min"
                :max="field.max"
                :step="field.type === 'money' ? '0.01' : field.step"
                :required="field.required && editingId === null"
                autocomplete="new-password"
                density="comfortable"
                persistent-hint
                variant="outlined"
              />
            </VCol>
          </VRow>

          <VAlert
            v-if="missingRequired.length > 0"
            type="warning"
            variant="tonal"
            density="compact"
            class="mt-2"
          >
            Required: {{ missingRequired.join(', ') }}
          </VAlert>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialogOpen = false">
            {{ t('cancel') }}
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="missingRequired.length > 0"
            @click="save"
          >
            {{ t('save') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
