<script setup lang="ts">
defineProps<{
  entityData?: User
}>()
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
              :value="entityData?.active ? ($t('enabled') || 'Enabled') : ($t('disabled') || 'Disabled')"
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
        </VList>
      </VCard>
    </div>
  </div>
</template>
