<script setup lang="ts">
defineProps({
  data: {
    type: Object,
    default: () => { return {} },
  },
})
</script>

<template>
  <VChip
    color="error"
    class="rounded"
    size="x-large"
    label
  >
    <VTooltip
      v-if="data.deletedReason"
      :text="data.deletedReason"
      width="300"
    >
      <template #activator="{ props }">
        <div v-bind="props">
          <VIcon
            start
            size="20"
            icon="tabler-alert-circle"
          />
        </div>
      </template>
    </VTooltip>
    <VTooltip
      v-if="data.deletedAt"
      :text="data.deletedAt"
    >
      <template #activator="{ props }">
        <div v-bind="props">
          <VListItemSubtitle :style="{ color: '#eb515d' }">
            {{ $t('deleted') }} | <b>{{ data.timeSince }}</b> | by <b>{{ data.deletedBy }}</b>
          </VListItemSubtitle>
        </div>
      </template>
    </VTooltip>
  </VChip>
</template>
