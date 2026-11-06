<script setup lang="ts">
/**
 * EQMS users-view pattern for Hotel entities:
 * header + AppTabs (hash `#info`) + edit/back links.
 */
const props = withDefaults(defineProps<{
  endpoint: string
  listPath: string
  entityLabel: string
  icon?: string
  fields: Array<HotelColumn & { label?: string }>
  titleKeys?: Array<string>
  canEdit?: boolean
  optionsLoaders?: Record<string, () => Promise<Array<HotelOption>>>
  /** Extra AppTabs after the Info tab (caller may close over entity). */
  buildExtraTabs?: (entity: Record<string, any>) => Array<AppTab>
}>(), {
  icon: 'tabler-building',
  titleKeys: () => ['name', 'room_number', 'bed_number', 'reference', 'code', 'id'],
  canEdit: true,
})

const route = useRoute()
const client = useHotelApiClient()

const id = computed(() => String(route.params.id))
const loading = ref(true)
const entity = ref<Record<string, any> | null>(null)
const tabItems = ref([] as AppTab[])
const resolvedOptions = ref<Record<string, Array<HotelOption>>>({})

const displayTitle = computed(() => {
  const row = entity.value
  if (!row)
    return props.entityLabel

  for (const key of props.titleKeys) {
    if (row[key] !== null && row[key] !== undefined && row[key] !== '')
      return String(row[key])
  }

  return `${props.entityLabel} #${id.value}`
})

const statusActive = computed(() => {
  if (!entity.value || entity.value.is_active === undefined)
    return null

  return Boolean(entity.value.is_active)
})

const loadOptions = async () => {
  if (!props.optionsLoaders)
    return

  const map: Record<string, Array<HotelOption>> = {}

  await Promise.all(Object.entries(props.optionsLoaders).map(async ([key, loader]) => {
    map[key] = await loader()
  }))

  resolvedOptions.value = map
}

const reloadData = async () => {
  loading.value = true

  await loadOptions()

  const row = await client.show<Record<string, any>>(props.endpoint, Number(id.value))

  entity.value = row

  const tabs: Array<AppTab> = [
    {
      icon: 'tabler-info-circle',
      tab: 'info',
      title: 'Info',
      pinnedTab: true,
      fields: props.fields,
      options: resolvedOptions.value,
      component: markRaw(resolveComponent('HotelViewInfo') as object),
    } as AppTab,
  ]

  if (row && props.buildExtraTabs)
    tabs.push(...props.buildExtraTabs(row))

  tabItems.value = tabs
  loading.value = false
}

reloadData()

watch(() => route.params.id, (next) => {
  if (next)
    reloadData()
})

const goEdit = () => {
  navigateTo({ path: props.listPath, query: { edit: id.value } })
}
</script>

<template>
  <div>
    <template v-if="loading">
      <div class="tw-mb-4">
        <VSkeletonLoader
          :loading="loading"
          type="heading@1"
        />
      </div>
      <div class="tw-grid tw-grid-cols-5 tw-gap-2 tw-justify-between">
        <div>
          <VSkeletonLoader
            :loading="loading"
            type="card"
          />
        </div>
        <div class="tw-col-span-4">
          <VSkeletonLoader
            :loading="loading"
            type="heading@4"
          />
        </div>
      </div>
    </template>

    <template v-else-if="entity">
      <VCard class="tw-grid tw-grid-cols-2 tw-justify-between tw-items-center tw-mb-6 w-100 sticky-header tw-p-4">
        <div class="tw-flex tw-gap-2">
          <VAvatar
            variant="tonal"
            :icon="icon"
            class="tw-mt-1"
            rounded
            :size="100"
          />
          <div>
            <div class="tw-text-2xl tw-font-bold tw-mb-2">
              {{ displayTitle }}
            </div>
            <div class="tw-text-base tw-font-semibold">
              <VChip
                size="small"
                variant="tonal"
              >
                {{ entityLabel }}
              </VChip>
              <template v-if="statusActive !== null">
                |
                <VChip
                  size="small"
                  variant="tonal"
                  :color="statusActive ? 'success' : 'error'"
                >
                  {{ statusActive ? 'Active' : 'Inactive' }}
                </VChip>
              </template>
              <template v-if="entity.status">
                |
                <VChip
                  size="small"
                  variant="tonal"
                  color="primary"
                >
                  {{ entity.status }}
                </VChip>
              </template>
            </div>
          </div>
        </div>

        <div class="tw-flex tw-gap-2 tw-justify-end">
          <VTooltip
            v-if="canEdit"
            text="Edit"
          >
            <template #activator="{ props: tip }">
              <div v-bind="tip">
                <VBtn
                  size="38"
                  color="primary"
                  @click="goEdit"
                >
                  <VIcon
                    icon="tabler-edit"
                    size="22"
                  />
                </VBtn>
              </div>
            </template>
          </VTooltip>
          <VBtn
            size="38"
            variant="tonal"
            color="secondary"
            @click="navigateTo(listPath)"
          >
            <VIcon
              icon="tabler-arrow-left"
              size="22"
            />
          </VBtn>
        </div>
      </VCard>

      <AppTabs
        v-if="tabItems.length"
        :tab-items="tabItems"
        :random-id="id"
        :entity-data="entity"
      />
    </template>

    <VAlert
      v-else
      type="error"
      variant="tonal"
    >
      {{ entityLabel }} not found.
      <VBtn
        class="ms-2"
        size="small"
        variant="text"
        @click="navigateTo(listPath)"
      >
        Back to list
      </VBtn>
    </VAlert>
  </div>
</template>
