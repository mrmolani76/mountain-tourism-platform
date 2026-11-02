<script setup>
defineProps({
  label: {
    type: String,
    required: false,
  },
  value: {
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
    <div>
      {{ displayOrFallback(value) }}
    </div>
  </div>
</template>
