<script setup lang="ts">
defineProps({
  itemData: { type: Object, required: true },
  linkeStyleOnHover: { type: Boolean, default: false },
})

const emits = defineEmits<{
  (e: 'getNestedListData'): void
  (e: 'totalCounts', count: number): void
}>()

const { t } = useI18n()
const actionDuePopoverMenu = ref(false)
const statusTextIcon = ref(false)
const showMoreHandle = ref(false)

const getPercentageColor = (percentage: number) => {
  if (percentage <= 33)
    return 'error'

  else if (percentage > 33 && percentage <= 66)
    return 'warning'

  else
    return 'success'
}
</script>

<template>
  <div>
    <div v-if="itemData.type === 'text'">
      <div class="tw-flex">
        <VTooltip :text="itemData.iconTooltip">
          <template #activator="{ props }">
            <VIcon
              v-if="itemData.icon"
              v-bind="props"
              :icon="itemData.icon"
              size="small"
              class="tw-mr-2"
            />
          </template>
        </VTooltip>
        <VMenu
          v-if="itemData.deletedData"
          location="top"
        >
          <template #activator="{ props }">
            <div v-bind="props">
              <div class="d-flex">
                <div>
                  <VIcon
                    icon="tabler-trash"
                    size="small"
                    color="error"
                  />
                </div>
              </div>
            </div>
          </template>
          <VCard
            maxWidth="1000"
            minWidth="300"
          >
            <VCardTitle class="tw-text-sm">
              {{ $t('deleted_detail') }}
            </VCardTitle>
            <VDivider
              thickness="2"
              class="border-opacity-50"
            />
            <VCardText class="tw-p-2">
              <ul class="tw-max-h-60 tw-overflow-y-auto">
                <li class="tw-mb-1">
                  <div class="d-flex tw-justify-between flex-wrap">
                    {{ $t('deleted_reason') }} - {{ itemData.deletedData.deletedReason }}
                  </div>
                </li>
                <li class="tw-mb-1">
                  <div class="d-flex tw-justify-between flex-wrap">
                    {{ $t('deleted_by') }} - {{ itemData.deletedData.deletedBy }}
                  </div>
                </li>
              </ul>
            </VCardText>
          </VCard>
        </VMenu>
        <span>
          {{ Array.isArray(itemData.label) ? itemData.label.join(', ') : itemData.label }}
        </span>
        <span v-if="itemData.append">{{ itemData.append }}</span>
      </div>
    </div>
    <div v-else-if="itemData.type === 'textWithRemark'">
      <span v-html="itemData.label" />
      <div>
        <i v-if="itemData.remark">{{ itemData.remark }}</i>
      </div>
    </div>
    <div v-else-if="itemData.type === 'linkWithActionRequried'">
      <VIcon
        icon="tabler-flag"
        size="small"
        color="warning"
      />
      <NuxtLink :to="itemData.url">
        <span
          :class="itemData.class"
          :style="itemData.style"
        >{{ itemData.label }}</span>
      </NuxtLink>
      <span v-if="itemData.append">{{ itemData.append }}</span>
    </div>
    <div v-else-if="itemData.type === 'link'">
      <div class="tw-flex">
        <VMenu
          v-if="itemData.deletedData"
          location="top"
        >
          <template #activator="{ props }">
            <div v-bind="props">
              <div class="d-flex">
                <VTooltip :text="itemData.iconTooltip ?? $t('this_role_has_been_remove')">
                  <template #activator="{ props }">
                    <div
                      v-bind="props"
                      class="tw-mr-2"
                    >
                      <VIcon
                        icon="tabler-info-circle"
                        size="small"
                        color="warning"
                      />
                    </div>
                  </template>
                </VTooltip>
              </div>
            </div>
          </template>
          <VCard
            maxWidth="1000"
            minWidth="300"
          >
            <VCardTitle class="tw-text-sm">
              {{ $t('deleted_detail') }}
            </VCardTitle>
            <VDivider
              thickness="2"
              class="border-opacity-50"
            />
            <VCardText class="tw-p-2">
              <ul class="tw-max-h-60 tw-overflow-y-auto">
                <li class="tw-mb-1">
                  <div class="d-flex tw-justify-between flex-wrap">
                    {{ $t('deleted_reason') }} - {{ itemData.deletedData.deletedReason }}
                  </div>
                </li>
                <li class="tw-mb-1">
                  <div class="d-flex tw-justify-between flex-wrap">
                    {{ $t('deleted_by') }} - {{ itemData.deletedData.deletedBy }}
                  </div>
                </li>
              </ul>
            </VCardText>
          </VCard>
        </VMenu>
        <NuxtLink
          :to="itemData.url"
          :target="itemData.target ? itemData.target : ''"
          :class="linkeStyleOnHover ? 'hover:tw-text-purple-900' : ''"
        >
          <VTooltip
            v-if="itemData.tooltip"
            :text="itemData.tooltip"
          >
            <template #activator="{ props }">
              <span
                :class="itemData.class"
                v-bind="props"
                :style="itemData.style"
              >{{ itemData.label
              }}</span>
            </template>
          </VTooltip>
          <span
            v-else
            :class="itemData.class"
            :style="itemData.style"
          >{{ itemData.label }}</span>
        </NuxtLink>
      </div>
      <span v-if="itemData.append">{{ itemData.append }}</span>
    </div>
    <div v-else-if="itemData.type === 'nestedLink'">
      <NuxtLink
        class="cursor-pointer"
        @click="emits('getNestedListData')"
      >
        {{ itemData.label }}
      </NuxtLink>
      <span v-if="itemData.append">{{ itemData.append }}</span>
    </div>
    <div v-else-if="itemData.type === 'badge'">
      <VBadge
        :color="itemData.color"
        :content="itemData.label"
      />
    </div>
    <div v-else-if="itemData.type === 'actionRequired'">
      <VIcon
        icon="tabler-flag"
        size="small"
        color="error"
      />
      {{ itemData.label }}
    </div>
    <div v-else-if="itemData.type === 'chip'">
      <VChip
        v-if="itemData.url"
        :variant="(itemData.variant) ? itemData.variant : 'elevated'"
        :color="(itemData.color) ? itemData.color : ''"
        :style="(itemData.style) ? itemData.style : ''"
        :class="(itemData.class) ? itemData.class : ''"
        :to="itemData.url"
      >
        {{ itemData.label }}
      </VChip>
      <VChip
        v-else
        :variant="(itemData.variant) ? itemData.variant : 'elevated'"
        :color="(itemData.color) ? itemData.color : ''"
        :style="(itemData.style) ? itemData.style : ''"
        :class="(itemData.class) ? itemData.class : ''"
      >
        {{ itemData.label }}
      </VChip>
    </div>
    <div
      v-else-if="itemData.type === 'separatedValues'"
      class="tw-flex tw-gap-1 tw-flex-wrap"
    >
      <span v-for="(value, index) in showMoreHandle ? itemData.data : itemData.data.slice(0, SHOW_MORE_MAX_LIMIT)">
        <DataTableCell :itemData="value" />
        <span v-if="index < itemData.data.length - 1" />
      </span>
      <div v-if="itemData.data && itemData.data.length > SHOW_MORE_MAX_LIMIT">
        <button @click="showMoreHandle = !showMoreHandle">
          {{ showMoreHandle ? $t("show_less") : $t("show_more") }}
        </button>
      </div>
    </div>
    <div v-else-if="itemData.type === 'commaSeparatedValues'">
      <span class="tw-flex tw-gap-1 tw-flex-wrap">
        <template v-for="(value, index) in showMoreHandle ? itemData.data : itemData.data.slice(0, SHOW_MORE_MAX_LIMIT)">
          <DataTableCell
            :itemData="value"
            :linkeStyleOnHover="true"
          />
          <span v-if="index < itemData.data.length - 1">,</span>
        </template>
      </span>
      <div v-if="itemData.data.length > SHOW_MORE_MAX_LIMIT">
        <button @click="showMoreHandle = !showMoreHandle">
          {{ showMoreHandle ? $t("show_less") : $t("show_more") }}
        </button>
      </div>
    </div>
    <div v-else-if="itemData.type === 'rating'">
      <div class="tw-flex tw-items-center tw-gap-1">
        <template v-if="itemData.value !== null && itemData.value !== undefined">
          <VRating
            :modelValue="Number(itemData.value)"
            :length="5"
            size="x-small"
            color="warning"
            activeColor="warning"
            density="compact"
            readonly
          />
        </template>
        <template v-else>
          <!-- No rating: keep cell visually empty -->
        </template>
      </div>
    </div>
    <div v-else-if="itemData.type === 'percentage'">
      <div class="tw-flex tw-gap-2 tw-items-center">
        <VProgressCircular
          :modelValue="itemData.label"
          :color="getPercentageColor(itemData.label)"
          size="large"
        >
          {{ itemData.label }}%
        </VProgressCircular>
      </div>
    </div>
    <div
      v-else-if="itemData.type === 'counts'"
      @click="emits('totalCounts', itemData.id)"
    >
      <VBtn
        variant="plain"
        class="tw-w-0 tw-p-0 tw-ms-1 tw-min-w-0"
      >
        {{ itemData.label }}
      </VBtn>
    </div>
    <div v-else-if="itemData.type === 'status'">
      <Status
        v-if="itemData.max"
        :max="itemData.max"
        :current="itemData.current"
        :totalUsers="itemData.totalUsers"
        :signedUsers="itemData.signedUsers"
        :label="itemData.label"
        :color="itemData.color"
        :skippedStatuses="itemData.skippedStatuses"
      />
      <VChip
        v-else
        :color="itemData.color"
        variant="elevated"
        label
      >
        {{ itemData.label }}
      </VChip>
    </div>
    <div v-else-if="itemData.type === 'iconTextTooltip'">
      <div class="tw-flex tw-gap-2 tw-items-center">
        <VTooltip :text="itemData.tooltip">
          <template #activator="{ props }">
            <VIcon
              :size="25"
              v-bind="props"
            >
              {{ itemData.icon }}
            </VIcon>
          </template>
        </VTooltip>
        <span>{{ itemData.label }}</span>
      </div>
    </div>
    <div v-else>
      <span v-html="itemData" />
    </div>
  </div>
</template>
