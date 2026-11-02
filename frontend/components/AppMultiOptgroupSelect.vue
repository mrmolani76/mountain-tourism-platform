<script setup>
import { computed, onMounted, ref, watch } from 'vue'

defineOptions({ name: 'AppMultiOptgroupSelect' })

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  items: { type: Array, required: true },
  label: { type: String, default: 'Select Options' },
  itemTitle: { type: String, default: 'title' },
  itemValue: { type: String, default: 'value' },
  selectAllLabel: { type: String, default: 'Select All' },
  rules: { type: Array, default: () => [] },
  returnObjects: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

// Flatten children for lookup
const allChildren = computed(() => props.items.flatMap(g => g.children || []))

const internalSelected = ref([])

const valueMap = computed(() => {
  const map = {}

  allChildren.value.forEach(c => { map[c[props.itemValue]] = c })

  return map
})

const isAllSelected = computed(() =>
  internalSelected.value.length === allChildren.value.length
  && allChildren.value.length > 0,
)

const toggleOption = val => {
  const index = internalSelected.value.findIndex(i => i[props.itemValue] === val)
  if (index > -1)
    internalSelected.value.splice(index, 1)
  else if (valueMap.value[val])
    internalSelected.value.push(valueMap.value[val])
}

const toggleSelectAll = () => {
  if (isAllSelected.value)
    internalSelected.value.splice(0)
  else
    internalSelected.value.splice(0, internalSelected.value.length, ...allChildren.value)
}

// 🔹 Helper: Sync from modelValue safely when data or items change
const syncFromModel = () => {
  if (!props.modelValue?.length || !allChildren.value.length)
    return

  let newSelected = []

  if (props.returnObjects) {
    newSelected = allChildren.value.filter(c =>
      props.modelValue.some(v => v[props.itemValue] === c[props.itemValue]),
    )
  }
  else {
    newSelected = allChildren.value.filter(c =>
      props.modelValue.includes(c[props.itemValue]),
    )
  }

  // Update only if changed
  if (JSON.stringify(newSelected) !== JSON.stringify(internalSelected.value))
    internalSelected.value = newSelected
}

// --- Watchers ---

// 1️⃣ Initial mount
onMounted(syncFromModel)

// 2️⃣ When parent updates v-model (manual assignment)
watch(() => props.modelValue, syncFromModel, { deep: true })

// 3️⃣ When items (groupedServices) arrive later via API
watch(() => props.items, syncFromModel, { deep: true, immediate: true })

// 4️⃣ Emit when internal selection changes
watch(
  internalSelected,
  val => {
    const emitValue = props.returnObjects
      ? val
      : val.map(v => v[props.itemValue])

    if (JSON.stringify(emitValue) !== JSON.stringify(props.modelValue))
      emit('update:modelValue', emitValue)
  },
  { deep: true },
)
</script>

<template>
  <VSelect
    v-model="internalSelected"
    :items="items"
    multiple
    clearable
    chips
    :itemTitle="itemTitle"
    :itemValue="itemValue"
    :rules="rules"
    hideDetails
  >
    <!-- Chips -->
    <template #selection="{ item }">
      <VChip
        v-if="item"
        :key="item[itemValue]"
        color="primary"
        variant="tonal"
        small
        class="ma-1"
        close
        @click:close="toggleOption(item[itemValue])"
      >
        {{ item[itemTitle] }}
      </VChip>
    </template>

    <!-- Select All -->
    <template #prepend-item>
      <template v-if="items.length > 0">
        <VListItem
          class="cursor-pointer"
          @click="toggleSelectAll"
        >
          <VCheckbox
            :modelValue="isAllSelected"
            :label="selectAllLabel"
            color="primary"
            hideDetails
            density="compact"
          />
        </VListItem>
        <VDivider class="my-1" />
      </template>
    </template>

    <!-- Groups -->
    <template #item="{ item }">
      <template v-if="item.raw.children">
        <VListItem
          class="group-header"
          disabled
        >
          <strong>{{ item.raw.title }}</strong>
        </VListItem>

        <VListItem
          v-for="child in item.raw.children"
          :key="child[itemValue]"
          class="cursor-pointer"
          @click="() => toggleOption(child[itemValue])"
        >
          <template #prepend>
            <VCheckbox
              :modelValue="internalSelected.some(i => i[itemValue] === child[itemValue])"
              hideDetails
              color="primary"
              density="compact"
            />
          </template>
          {{ child[itemTitle] }}
        </VListItem>
      </template>
    </template>
  </VSelect>
</template>

<style scoped>
.cursor-pointer { cursor: pointer; }

.group-header {
  background-color: #f5f5f5;
  border-block-start: 1px solid #e0e0e0;
  color: #424242;
  font-weight: 600;
  padding-block: 6px;
}
</style>
