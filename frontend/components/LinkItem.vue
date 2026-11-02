<script setup lang="ts">
defineProps<{
  item: LinkEntity
  label: string
}>()

const showMoreHandle = ref(false);
</script>

<template>
  <div class="tw-flex tw-gap-2">
    <div class="tw-font-normal tw-flex-shrink-0 tw-font-semibold">
      {{ $t(label) }}
    </div>
    :
    <template v-if="Array.isArray(item)">
      <template
        v-for="(data, index) in showMoreHandle ? item : item.slice(0, SHOW_MORE_MAX_LIMIT)"
        :key="index"
      >
        <span v-if="data?.type === 'text'">{{ displayOrFallback(data?.label) }}</span>
        <NuxtLink
          v-else-if="data?.type === 'link'"
          :to="data?.url"
        >
          <span class="tw-font-normal">{{ displayOrFallback(data?.label) }}</span>
        </NuxtLink>
        <span v-if="index < item.length - 1">,</span>
        <div v-if="item && item.length > SHOW_MORE_MAX_LIMIT">
          <button @click="showMoreHandle = !showMoreHandle">
            {{ showMoreHandle ? $t("show_less") : $t("show_more") }}
          </button>
        </div>
      </template>
    </template>
    <template v-else>
      <div v-if="item?.type === 'text'">
        {{ displayOrFallback(item?.label) }}
      </div>
      <div v-else-if="item?.type === 'link'">
        <NuxtLink :to="item?.url">
          <span class="tw-font-normal">{{ displayOrFallback(item?.label) }}</span>
        </NuxtLink>
      </div>
    </template>
  </div>
</template>
