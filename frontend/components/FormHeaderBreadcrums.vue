<script setup lang="ts">
const props = defineProps({
  page: String,
  action: String,
  uuid: {
    type: String,
    default: null,
  },
})

const { t } = useI18n();

const breadcrumbs = computed(() => {
  return [
    {
      title: t(props.page),
      disabled: true,
    },
    {
      title: props.action,
      disabled: false,
    },
  ]
})
</script>

<template>
  <div>
    <div class="d-flex">
      <VBreadcrumbs
        color="primary"
        class="tw-text-lg"
        :items="breadcrumbs"
      >
        <template #divider>
          <VIcon icon="tabler-chevrons-right" />
        </template>
      </VBreadcrumbs>
    </div>
  </div>
</template>
