<script setup>
defineProps({
  label: {
    type: String,
    required: false,
  },
  chips: {
    type: null,
    required: true,
  },
})
</script>

<template>
  <div class="tw-flex tw-gap-2">
    <div class="tw-font-semibold tw-flex-shrink-0">
      {{ $t(label) }}
    </div>
    :
    <div class="flex flex-wrap gap-2">
      <VChip
        v-for="(item, index) in chips"
        :key="index"
        :label="false"
        color="primary"
        class="mr-2 mb-2"
      >
        {{ item }}
      </VChip>
    </div>
  </div>
</template>
