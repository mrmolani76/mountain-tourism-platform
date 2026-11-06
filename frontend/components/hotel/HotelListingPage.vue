<script setup lang="ts">
/**
 * EQMS DataTable listing for Hotel resources.
 * Config + paged rows from `/api/hotel/{resource}/list/config` + `/list`.
 * Create / edit still uses the dialog form (fields prop).
 */
import type { VForm } from 'vuetify/components/VForm'

const props = withDefaults(defineProps<{
  resource: string
  title: string
  subtitle?: string
  endpoint: string
  viewPath: string
  fields: Array<HotelFormField>
  defaults?: Record<string, any>
  addLabel?: string
  canCreate?: boolean
  canEdit?: boolean
  canDelete?: boolean
}>(), {
  subtitle: '',
  defaults: () => ({}),
  addLabel: 'Add',
  canCreate: true,
  canEdit: true,
  canDelete: true,
})

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const client = useHotelApiClient()
const { post, get } = useApiClient()

const configLoading = ref(true)
const listLoading = ref(false)
const tableConfig = ref<DataTableConfig | null>(null)
const listResult = ref<DataTableResult<any>>({
  status: false,
  data: [],
  pagination: { total: 0 },
})
const tableParams = ref<Record<string, any>>({})

const dialogOpen = ref(false)
const saving = ref(false)
const editingId = ref<number | null>(null)
const form = ref<Record<string, any>>({})
const resolvedOptions = ref<Record<string, Array<HotelOption>>>({})
const refForm = ref<VForm>()

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

const loadConfig = async () => {
  configLoading.value = true
  try {
    const result = await get<ApiResponse<DataTableConfig>>(`hotel/${props.resource}/list/config`)

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
    const result = await post<DataTableResult<any>>(`hotel/${props.resource}/list`, params)

    if (result?.status)
      listResult.value = result
  }
  finally {
    listLoading.value = false
  }
}

const openCreate = async () => {
  editingId.value = null
  form.value = blankForm()
  dialogOpen.value = true
  await nextTick()
  refForm.value?.resetValidation()
}

const openEdit = async (row: Record<string, any>) => {
  const id = Number(row.id ?? row.uuid)
  const full = await client.show<Record<string, any>>(props.endpoint, id)
  if (!full)
    return

  editingId.value = id
  const values: Record<string, any> = { ...full }

  props.fields.forEach((field) => {
    if (field.type === 'money')
      values[field.key] = centsToMajor(full[field.key])
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
      const savedId = Number((response as any).id ?? editingId.value)
      if (savedId)
        await navigateTo(`${props.viewPath.replace(/\/$/, '')}/view/${savedId}`)
      else
        await reloadListingData()
    }
  }
  finally {
    saving.value = false
  }
}

const destroy = async (row: Record<string, any>) => {
  const id = Number(row.id ?? row.uuid)
  const confirmed = await showConfirmDialog(`Delete #${id}?`, t('delete'), t('cancel'))
  if (!confirmed)
    return

  await client.remove(props.endpoint, id)
  await reloadListingData()
}

const openEditFromQuery = async () => {
  const editId = route.query.edit
  if (!editId)
    return

  await openEdit({ id: editId })
  const query = { ...route.query }
  delete query.edit
  await router.replace({ path: route.path, query })
}

onMounted(async () => {
  await Promise.all([loadConfig(), loadOptions()])
  await openEditFromQuery()
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

    <template v-else>
      <DataTable
        v-bind="tableConfig"
        v-model="tableParams"
        :list-data="listResult.data"
        :total-items="listResult.pagination?.total ?? 0"
        :loading="listLoading"
        :show-add-button="canCreate && (tableConfig as any).showAddButton !== false"
        :add-new-button-label="addLabel"
        checkbox-id-key="id"
        @load-items="reloadListingData"
        @add-new="openCreate"
      >
        <template #actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn
              v-if="item.actions?.viewPermission"
              size="38"
              color="primary"
              @click="navigateTo(`${viewPath.replace(/\/$/, '')}/view/${item.id}`)"
            >
              <VTooltip activator="parent">
                {{ t('view') }}
              </VTooltip>
              <VIcon
                icon="tabler-eye"
                size="22"
              />
            </VBtn>
            <VBtn
              v-if="canEdit && item.actions?.writePermission"
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
              v-if="canDelete && item.actions?.writePermission"
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
    </template>

    <VDialog
      v-model="dialogOpen"
      max-width="720"
      persistent
    >
      <VCard>
        <VCardTitle>
          {{ editingId === null ? addLabel : `Edit ${title.replace(/s$/, '')}` }}
        </VCardTitle>
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
                    :required="field.required"
                    rows="3"
                  />
                  <AppTextField
                    v-else
                    v-model="form[field.key]"
                    :type="field.type === 'number' || field.type === 'money' ? 'number' : (field.type === 'email' ? 'email' : 'text')"
                    :rules="fieldRules(field)"
                    :required="field.required"
                    :min="field.min"
                    :max="field.max"
                    :step="field.step ?? (field.type === 'money' ? 0.01 : undefined)"
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
