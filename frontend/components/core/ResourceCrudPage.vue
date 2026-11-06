<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'

/**
 * Core CRUD with EQMS DataTable listing + dialog form.
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

const configLoading = ref(true)
const listLoading = ref(false)
const tableConfig = ref<DataTableConfig | null>(null)
const listResult = ref<DataTableResult<any>>({
  status: false,
  data: [],
  pagination: { total: 0 },
})
const tableParams = ref<Record<string, any>>({})
const rawRows = ref<Array<Record<string, any>>>([])

const dialogOpen = ref(false)
const saving = ref(false)
const editingId = ref<number | null>(null)
const form = ref<Record<string, any>>({})
const resolvedOptions = ref<Record<string, Array<HotelOption>>>({})
const refForm = ref<VForm>()

const dialogTitle = computed(() => editingId.value === null ? props.addLabel : `Edit ${props.title.replace(/s$/, '')}`)

const fieldRules = (field: HotelFormField) => {
  if (!field.required || field.type === 'checkbox')
    return []

  if (field.type === 'password' && editingId.value !== null)
    return []

  return [requiredValidator]
}

const isBlank = (value: unknown) =>
  value === null || value === undefined || value === ''

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

const applyListRows = (rows: Array<Record<string, any>>) => {
  rawRows.value = rows
  listResult.value = {
    status: true,
    data: rows.map(row => mapRowToDataTableCells(row, props.columns, {
      optionLabels: resolvedOptions.value,
    })),
    pagination: { total: rows.length },
  }
}

const reloadListingData = async (params: any = tableParams.value) => {
  listLoading.value = true
  tableParams.value = params || {}

  try {
    const page = Number(params?.page || 1)
    const pageSize = Number(params?.itemsPerPage || params?.page_size || 25)

    const { data, meta } = await client.list(props.endpoint, {
      page,
      page_size: pageSize === -1 ? 100 : pageSize,
      ...props.listParams,
    })

    const rows = (data as Array<Record<string, any>>) ?? []

    applyListRows(rows)
    listResult.value.pagination = { total: Number(meta?.total_count ?? rows.length) }
  }
  finally {
    listLoading.value = false
    configLoading.value = false
  }
}

const openCreate = async () => {
  editingId.value = null
  form.value = blankForm()
  dialogOpen.value = true
  await nextTick()
  refForm.value?.resetValidation()
}

const openEdit = async (item: Record<string, any>) => {
  const id = Number(item.id ?? item.uuid)
  const row = rawRows.value.find(entry => Number(entry.id) === id)
    ?? await client.show<Record<string, any>>(props.endpoint, id)

  if (!row)
    return

  editingId.value = id
  const values: Record<string, any> = { ...row }

  props.fields.forEach((field) => {
    if (field.type === 'money')
      values[field.key] = centsToMajor(row[field.key])

    if (field.type === 'multiselect' && !Array.isArray(values[field.key]))
      values[field.key] = []

    if (field.type === 'password')
      values[field.key] = ''
  })

  form.value = values
  dialogOpen.value = true
  await nextTick()
  refForm.value?.resetValidation()
}

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

    if (field.type === 'password' && isBlank(value))
      return

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
      await reloadListingData()
    }
  }
  finally {
    saving.value = false
  }
}

const destroy = async (item: Record<string, any>) => {
  const id = Number(item.id ?? item.uuid)
  const confirmed = await showConfirmDialog(`Delete #${id}?`, t('delete'), t('cancel'))

  if (!confirmed)
    return

  await client.remove(props.endpoint, id)
  await reloadListingData()
}

onMounted(async () => {
  tableConfig.value = buildDataTableConfig(props.columns)
  await loadOptions()
  await reloadListingData({ page: 1, itemsPerPage: 25 })
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
      :loading="listLoading"
      :show-add-button="canCreate"
      :add-new-button-label="addLabel"
      checkbox-id-key="id"
      @load-items="reloadListingData"
      @add-new="openCreate"
    >
      <template #actions="{ item }">
        <div class="d-flex gap-1">
          <VBtn
            v-if="canEdit"
            size="38"
            color="primary"
            @click="openEdit(item)"
          >
            <VTooltip activator="parent">
              {{ t('edit') }}
            </VTooltip>
            <VIcon
              icon="tabler-edit"
              size="22"
            />
          </VBtn>
          <VBtn
            v-if="canDelete"
            size="38"
            color="error"
            variant="tonal"
            @click="destroy(item)"
          >
            <VTooltip activator="parent">
              {{ t('delete') }}
            </VTooltip>
            <VIcon
              icon="tabler-trash"
              size="22"
            />
          </VBtn>
        </div>
      </template>
    </DataTable>

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
                      v-if="field.required && !(field.type === 'password' && editingId !== null)"
                      class="text-error font-weight-bold ms-1"
                    >*</span>
                  </VLabel>

                  <AppSelect
                    v-if="field.type === 'select'"
                    v-model="form[field.key]"
                    :items="resolvedOptions[field.key] ?? field.options ?? []"
                    item-title="title"
                    item-value="value"
                    :rules="fieldRules(field)"
                    :required="field.required"
                    clearable
                    density="comfortable"
                  />
                  <AppSelect
                    v-else-if="field.type === 'multiselect'"
                    v-model="form[field.key]"
                    :items="resolvedOptions[field.key] ?? field.options ?? []"
                    item-title="title"
                    item-value="value"
                    :rules="fieldRules(field)"
                    chips
                    multiple
                    density="comfortable"
                  />
                  <AppTextarea
                    v-else-if="field.type === 'textarea'"
                    v-model="form[field.key]"
                    :rules="fieldRules(field)"
                    rows="3"
                  />
                  <AppTextField
                    v-else
                    v-model="form[field.key]"
                    :type="field.type === 'password' ? 'password' : (field.type === 'number' || field.type === 'money' ? 'number' : (field.type === 'email' ? 'email' : (field.type === 'date' ? 'date' : 'text')))"
                    :rules="fieldRules(field)"
                    :required="field.required"
                    density="comfortable"
                  />
                </template>
              </VCol>
            </VRow>
          </VCardText>
          <VCardActions>
            <VSpacer />
            <VBtn
              variant="tonal"
              color="secondary"
              @click="dialogOpen = false"
            >
              {{ t('cancel') }}
            </VBtn>
            <VBtn
              color="primary"
              type="submit"
              :loading="saving"
            >
              {{ t('save') }}
            </VBtn>
          </VCardActions>
        </VForm>
      </VCard>
    </VDialog>
  </div>
</template>
