<script setup lang="ts">
/**
 * EQMS-style vertical + horizontal entity tabs.
 * Left rail = all tabs; top strip = open/pinned tabs; hash `#tabKey` deep-links.
 */
interface Emit {
  (e: 'postSubmit'): void
}

const props = defineProps({
  tabItems: {
    type: Array as () => AppTab[],
    required: true,
  },
  randomId: {
    type: String,
    required: true,
  },
  entityId: {
    type: String,
    required: false,
  },
  entityData: {
    type: Object,
    required: false,
  },
  majorVersion: {
    type: String,
  },
  isShowDeleted: {
    type: Boolean,
    required: false,
  },
  isTabRefresh: {
    type: Boolean,
    required: false,
  },
})

const emit = defineEmits<Emit>()
const router = useRouter()
const componentKey = ref(0)

const activeTab = ref<string | null>(null)
const activeInnerTab = ref('')
const { t } = useI18n()

const defaultTabItems = ref([] as AppTab[])

const items = computed(() => (props.tabItems ?? []) as AppTab[])

const findTab = (key: string) =>
  items.value.find(x => String(x.tab).toLowerCase() === key.toLowerCase())

const addTabToDefaultItems = (newValue: AppTab) => {
  activeInnerTab.value = ''

  const alreadyOpen = defaultTabItems.value.some(x => x.tab === newValue.tab)

  if (alreadyOpen) {
    activeTab.value = newValue.tab

    return
  }

  const tempObj = { ...newValue, title: t(newValue.title) }

  defaultTabItems.value.push(tempObj)
  activeTab.value = newValue.tab
}

const addTab = (newValue: AppTab) => {
  pushHashIntoRoute(newValue.tab)
  addTabToDefaultItems(newValue)
}

const addEntityTab = (newValue: AppTab) => {
  // Linked entity tabs share one hash; do not call pushHashIntoRoute.
  const item = defaultTabItems.value.find(x => x.tab === newValue.tab)

  if (item) {
    activeTab.value = newValue.tab

    return
  }

  const tempObj = {
    ...newValue,
    title: t(newValue.title),
    component: resolveComponent('QCEntityLinkedTab'),
  }

  defaultTabItems.value.push(tempObj as AppTab)
  activeTab.value = defaultTabItems.value[defaultTabItems.value.length - 1].tab
}

const removeTab = async (newValue: AppTab) => {
  activeTab.value = null

  const index = defaultTabItems.value.indexOf(newValue)

  if (index >= 0)
    defaultTabItems.value.splice(index, 1)

  setTimeout(() => {
    const last = defaultTabItems.value[defaultTabItems.value.length - 1]

    activeTab.value = last?.tab ?? null
    if (activeTab.value)
      pushHashIntoRoute(activeTab.value)
  }, 0)
}

const addInnerTab = (newValue: AppTab, innertab: any = null, allInnerTabData: any = []) => {
  pushHashIntoRoute(newValue.tab)

  const item = defaultTabItems.value.find(x => x.tab === newValue.tab) as any

  if (item) {
    activeTab.value = newValue.tab
    item.innertab = innertab?.tab
    item.allInnerTabData = allInnerTabData?.tab

    return
  }

  const tempObj = {
    ...newValue,
    title: t(newValue.title),
    innertab: innertab?.tab,
  }

  defaultTabItems.value.push(tempObj as AppTab)
  activeTab.value = defaultTabItems.value[defaultTabItems.value.length - 1].tab
}

const loadTabs = () => {
  if (!items.value.length)
    return

  defaultTabItems.value = items.value.filter(x => x.pinnedTab)

  const hashUrl = router.currentRoute.value.hash

  if (hashUrl) {
    const key = hashUrl.replace('#', '')
    const hashTab = findTab(key)

    if (hashTab) {
      addTabToDefaultItems(hashTab)

      return
    }
  }

  activeTab.value = defaultTabItems.value.find(x => x.pinnedTab)?.tab ?? null
  componentKey.value++
}

watch(() => items.value.length, () => {
  loadTabs()
}, { immediate: true })

// Keep open tab in sync when browser back/forward changes the hash.
watch(() => router.currentRoute.value.hash, (hash) => {
  if (!hash || !items.value.length)
    return

  const key = hash.replace('#', '')
  const hashTab = findTab(key)

  if (hashTab)
    addTabToDefaultItems(hashTab)
})
</script>

<template>
  <div>
    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-1 md:tw-grid-cols-1 lg:tw-grid-cols-5 tw-gap-2">
      <div>
        <VCard class="tw-mb-2">
          <slot name="middleColumnData" />
        </VCard>
        <VCard>
          <VTabs
            v-model="activeTab"
            direction="vertical"
            class="v-tabs-pill"
          >
            <VTab
              v-for="item in items"
              :key="item.tab"
              :value="item.tab"
              @click="addTab(item)"
            >
              <VAvatar
                v-if="item.imgIcon"
                size="22"
                :image="item.imgIcon"
              />
              <VIcon
                size="20"
                start
                :icon="item.icon"
              />
              <div>
                {{ t(item.title) }}
                <VChip
                  v-if="item.count"
                  size="x-small"
                  :color="activeTab === item.tab ? 'white' : 'primary'"
                  class="tw-ml-2"
                >
                  {{ item.count }}
                </VChip>
              </div>
              <VMenu
                v-if="item.expandable"
                open-on-hover
                class="tw-ml-8"
                activator="parent"
                location="end center"
              >
                <VCard>
                  <VList
                    v-for="item1 in item.expandData"
                    :key="item1.title"
                    v-model="activeInnerTab"
                  >
                    <VListItem @click="addInnerTab(item, item1, item.expandData)">
                      {{ item1.title }}
                      <VChip
                        v-if="item1.count"
                        size="x-small"
                        color="primary"
                        class="tw-ml-2"
                      >
                        {{ item1.count }}
                      </VChip>
                    </VListItem>
                  </VList>
                </VCard>
              </VMenu>
            </VTab>
          </VTabs>
        </VCard>
      </div>

      <div class="lg:tw-col-span-4 sm:tw-col-span-2 md:tw-col-span-4">
        <VCard>
          <VTabs v-model="activeTab">
            <VTab
              v-for="item in defaultTabItems"
              :key="item.tab"
              :value="item.tab"
              @click="pushHashIntoRoute(item.tab)"
            >
              <div>
                <VAvatar
                  v-if="item.imgIcon"
                  size="22"
                  :image="item.imgIcon"
                />
                <VIcon
                  v-else
                  size="20"
                  start
                  :icon="item.icon"
                />
                {{ t(item.title) }}
              </div>
              <IconBtn
                v-if="!item.pinnedTab"
                size="20"
                @click.stop="removeTab(item)"
              >
                <VIcon
                  icon="tabler-x"
                  size="15"
                />
              </IconBtn>
            </VTab>
          </VTabs>
        </VCard>

        <VWindow
          v-model="activeTab"
          class="mt-2 disable-tab-transition"
          :touch="false"
        >
          <VWindowItem
            v-for="item in defaultTabItems"
            :key="item.tab"
            :value="item.tab"
          >
            <component
              :is="item.component"
              v-if="item.tab === activeTab"
              :key="`${item.tab}-${componentKey}`"
              v-bind="{ ...item }"
              :item-data="item"
              :entity-random-id="randomId"
              :entity-data="entityData"
              :is-show-deleted="isShowDeleted"
              @add-entity-tab="addEntityTab"
              @post-submit="emit('postSubmit')"
            />
          </VWindowItem>
        </VWindow>
      </div>
    </div>
  </div>
</template>
