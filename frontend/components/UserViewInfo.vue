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
          {{ $t('personalInformation') }}
        </div>
        <VDivider
          :thickness="1"
          class="border-opacity-100"
          color="secondary"
        />
        <VList width="100%">
          <VListItem>
            <LabelValueDisplay
              label="firstName"
              :value="entityData?.firstName"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="lastName"
              :value="entityData?.lastName"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="email"
              :value="entityData?.email"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="phoneNo"
              :value="entityData?.phoneNo"
            />
          </VListItem>
        </VList>
      </VCard>
    </div>

    <div>
      <VCard>
        <div class="tw-mb-4 tw-text-lg tw-mt-4 tw-ml-4">
          {{ $t('roleInformation') }}
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
          <VListItem v-if="entityData?.entityLabel || entityData?.entity">
            <LinkItem
              :label="entityData?.entityLabel"
              :item="entityData?.entity"
            />
          </VListItem>
          <VListItem v-if="entityData?.driverIC">
            <LabelValueDisplay
              label="driverIC"
              :value="entityData?.driverIC"
            />
          </VListItem>
        </VList>
      </VCard>
    </div>
  </div>
</template>
