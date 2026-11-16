<script setup lang="ts">
const props = defineProps<{
  entityData?: User & { outlet_ids?: number[]; is_active?: boolean }
}>()

const isActive = computed(() =>
  props.entityData?.active ?? props.entityData?.is_active ?? false,
)

const outletIdsLabel = computed(() => {
  const ids = props.entityData?.outlet_ids
  if (!Array.isArray(ids) || !ids.length)
    return '—'

  return ids.join(', ')
})
</script>

<template>
  <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-2">
    <div>
      <VCard>
        <div class="tw-mb-4 tw-text-lg tw-mt-4 tw-ml-4">
          {{ $t('personalInformation') || 'Account' }}
        </div>
        <VDivider
          :thickness="1"
          class="border-opacity-100"
          color="secondary"
        />
        <VList width="100%">
          <VListItem>
            <LabelValueDisplay
              label="email"
              :value="entityData?.email"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="status"
              :value="isActive ? ($t('enabled') || 'Enabled') : ($t('disabled') || 'Disabled')"
            />
          </VListItem>
        </VList>
      </VCard>
    </div>

    <div>
      <VCard>
        <div class="tw-mb-4 tw-text-lg tw-mt-4 tw-ml-4">
          {{ $t('roleInformation') || 'Role' }}
        </div>
        <VDivider
          :thickness="1"
          class="border-opacity-100"
          color="secondary"
        />
        <VList width="100%">
          <VListItem>
            <LabelValueDisplay
              label="role"
              :value="entityData?.role?.roleName"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="Outlet access"
              :value="outletIdsLabel"
            />
          </VListItem>
        </VList>
      </VCard>
    </div>
  </div>
</template>
