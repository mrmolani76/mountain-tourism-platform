<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'

/**
 * Generic CRUD page for simple Hotel resources (room types, rooms, dormitories, beds,
 * season prices, board plans). Form layout follows the SPA pattern:
 * external VLabel + red required mark, App* fields, VForm rules, then save.
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
const client = useHotelApiClient()

const rows = ref<Array<Record<string, any>>>([])
const initialLoading = ref(true)
const loading = ref(false)
const search = ref('')
const dialogOpen = ref(false)
const saving = ref(false)
const editingId = ref<number | null>(null)
const form = ref<Record<string, any>>({})
const resolvedOptions = ref<Record<string, Array<HotelOption>>>({})
const refForm = ref<VForm>()

const headers = computed(() => [
  ...props.columns,
  { key: 'actions', title: '', sortable: false, align: 'end' as const },
])

const dialogTitle = computed(() => editingId.value === null ? props.addLabel : `Edit ${props.title.replace(/s$/, '')}`)

const fieldRules = (field: HotelFormField) => {
  if (!field.required || field.type === 'checkbox')
    return []

  return [requiredValidator]
}

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

const openCreate = async () => {
  editingId.value = null
  form.value = blankForm()
  dialogOpen.value = true
  await nextTick()
  refForm.value?.resetValidation()
}

const openEdit = async (row: Record<string, any>) => {
  editingId.value = Number(row.id)

  const values: Record<string, any> = { ...row }

  props.fields.forEach((field) => {
    if (field.type === 'money')
      values[field.key] = centsToMajor(row[field.key])

    if (field.type === 'multiselect' && !Array.isArray(values[field.key]))
      values[field.key] = []
  })

  form.value = values
  dialogOpen.value = true
  await nextTick()
  refForm.value?.resetValidation()
}

const isBlank = (value: unknown) =>
  value === null || value === undefined || value === ''

const buildPayload = () => {
  const payload: Record<string, any> = {}

  props.fields.forEach((field) => {
    const value = form.value[field.key]

    if (field.type === 'money') {
      if (isBlank(value) && !field.required)
        return

      payload[field.key] = majorToCents(value)

      return
    }

    if (field.type === 'number' || field.key.endsWith('_id')) {
      if (isBlank(value)) {
        if (field.required)
          payload[field.key] = value

        return
      }

      payload[field.key] = Number(value)

      return
    }

    if (field.type === 'checkbox') {
      payload[field.key] = Boolean(value)

      return
    }

    if (field.type === 'multiselect') {
      payload[field.key] = Array.isArray(value) ? value : []

      return
    }

    // Omit empty optional strings so Laravel `sometimes`/`integer` rules do not see null/"".
    if (isBlank(value) && !field.required)
      return

    payload[field.key] = value
  })

  return payload
}

const save = async () => {
  const { valid } = await refForm.value!.validate()
  if (!valid)
    return

  saving.value = true
  try {
    const payload = buildPayload()
    const response = editingId.value === null
      ? await client.create(props.endpoint, payload)
      : await client.update(props.endpoint, editingId.value, payload)

    if (response) {
      dialogOpen.value = false
      await load()
    }
  }
  finally {
    saving.value = false
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
        <p
          v-if="subtitle"
          class="text-body-2 text-medium-emphasis mb-0"
        >
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
            <td
              v-for="column in columns"
              :key="column.key"
              :class="column.align === 'end' ? 'text-end' : ''"
            >
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

    <VDialog
      v-model="dialogOpen"
      max-width="720"
      persistent
    >
      <VCard>
        <VCardTitle>{{ dialogTitle }}</VCardTitle>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VCardText>
            <VRow>
              <VCol
                v-for="field in fields"
                :key="field.key"
                :cols="12"
                :md="field.col ?? 6"
              >
                <template v-if="field.type === 'checkbox'">
                  <VCheckbox
                    v-model="form[field.key]"
                    :label="field.label"
                    hide-details
                    class="mt-4"
                  />
                </template>

                <template v-else>
                  <VLabel class="mb-1 text-body-2 text-wrap d-inline-flex align-center">
                    {{ field.label }}
                    <span
                      v-if="field.required"
                      class="text-error font-weight-bold ms-1"
                    >*</span>
                  </VLabel>

                  <AppSelect
                    v-if="field.type === 'select'"
                    v-model="form[field.key]"
                    :items="resolvedOptions[field.key] ?? field.options ?? []"
                    item-title="title"
                    item-value="value"
                    :hint="field.hint"
                    :rules="fieldRules(field)"
                    :required="field.required"
                    clearable
                    density="comfortable"
                    persistent-hint
                  />

                  <AppSelect
                    v-else-if="field.type === 'multiselect'"
                    v-model="form[field.key]"
                    :items="resolvedOptions[field.key] ?? field.options ?? []"
                    item-title="title"
                    item-value="value"
                    :hint="field.hint"
                    :rules="fieldRules(field)"
                    :required="field.required"
                    chips
                    closable-chips
                    density="comfortable"
                    multiple
                    persistent-hint
                  />

                  <AppTextarea
                    v-else-if="field.type === 'textarea'"
                    v-model="form[field.key]"
                    :hint="field.hint"
                    :rules="fieldRules(field)"
                    :required="field.required"
                    density="comfortable"
                    persistent-hint
                    rows="2"
                  />

                  <AppTextField
                    v-else
                    v-model="form[field.key]"
                    :type="field.type === 'money' || field.type === 'number' ? 'number' : (field.type ?? 'text')"
                    :hint="field.hint"
                    :min="field.min"
                    :max="field.max"
                    :step="field.type === 'money' ? '0.01' : field.step"
                    :rules="fieldRules(field)"
                    :required="field.required"
                    density="comfortable"
                    persistent-hint
                  />
                </template>
              </VCol>
            </VRow>
          </VCardText>

          <VCardActions>
            <VSpacer />
            <VBtn
              variant="text"
              :disabled="saving"
              @click="dialogOpen = false"
            >
              {{ t('cancel') }}
            </VBtn>
            <VBtn
              color="primary"
              type="submit"
              :loading="saving"
              :disabled="saving"
            >
              {{ t('save') }}
            </VBtn>
          </VCardActions>
        </VForm>
      </VCard>
    </VDialog>
  </div>
</template>
