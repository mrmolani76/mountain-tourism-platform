<script setup lang="ts">
/**
 * Form header crumbs. Listing title links back to the module index
 * so create/edit stays connected to the list page.
 */
const props = defineProps({
  page: String,
  action: String,
  uuid: {
    type: String,
    default: null,
  },
})

const { t } = useI18n()

const breadcrumbs = computed(() => {
  return [
    {
      title: t(props.page || ''),
      disabled: false,
      to: `/${props.page}`,
    },
    {
      title: props.action,
      disabled: true,
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
