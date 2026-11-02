<script setup lang="ts">
const { t } = useI18n();

definePageMeta({
  navActiveLink: 'users',
  checkPermission: 'users.view',
})

useHead({
  title: t('userDetail'),
})

const { getUserViewById, loading } = useUserApiClient();

const route = useRoute()
const uuid = ref(route.params.id);
const user = ref({} as User | undefined);
const permission = ref({} as Permission | undefined);
const tabItems = ref([] as AppTab[]);
const userProfile = ref();

(async () => {
  const response = await getUserViewById(uuid.value);

  user.value = response?.user
  permission.value = response?.permissions;
  userProfile.value = response?.userProfile;
  tabItems.value = [
    {
      icon: 'tabler-info-circle',
      tab: 'userInfo',
      title: t('info'),
      pinnedTab: true,
      component: markRaw(resolveComponent('UserViewInfo') as object),
    },
    {
      icon: 'tabler-list-search',
      tab: 'audittrail',
      title: t('auditTrail'),
      module: MODULE_CORE,
      submodule: SUBMODULE_USERS,
      identifier: IDENTIFIER_AUDIT_TRAIL_USERS_LISTING_FILTER,
      filterName: 'auditTrailUsersFilters',
      component: markRaw(resolveComponent('EntityAuditTrail') as object),
    },
  ]
})()
</script>

<template>
  <div>
    <template v-if="loading">
      <div class="tw-mb-4">
        <VSkeletonLoader
          :loading="loading"
          type="heading@1"
        />
      </div>
      <div class="tw-grid tw-grid-cols-5 tw-gap-2 tw-justify-between">
        <div>
          <VSkeletonLoader
            :loading="loading"
            type="card"
          />
          <VSkeletonLoader
            :loading="loading"
            type="heading@4"
            class="tw-mt-2"
          />
        </div>
        <div class="tw-col-span-4">
          <VSkeletonLoader
            :loading="loading"
            type="heading@1"
            class="tw-mb-4"
          />
          <VSkeletonLoader
            :loading="loading"
            type="heading@4"
          />
        </div>
      </div>
    </template>
    <template v-else>
      <VCard class="tw-grid tw-grid-cols-2 tw-justify-between tw-items-center tw-mb-6 w-100 sticky-header tw-p-4">
        <div class="tw-flex tw-gap-2">
          <VAvatar
            v-if="userProfile"
            rounded
            :size="100"
          >
            <VImg :src="userProfile">
              <template #placeholder>
                <div class="tw-flex tw-items-center tw-justify-center tw-h-full">
                  <VProgressCircular
                    indeterminate
                    color="primary"
                  />
                </div>
              </template>
            </VImg>
          </VAvatar>
          <VAvatar
            v-else
            variant="tonal"
            icon="tabler-user"
            class="tw-mt-1"
            rounded
            :size="100"
          />
          <div>
            <div class="tw-text-2xl tw-font-bold tw-mb-2">
              {{ user?.fullName }}
            </div>
            <div class="tw-text-base tw-font-semibold">
              <VChip size="small">
                {{ $t(displayOrFallback(user?.role?.roleName)) }}
              </VChip>
              |
              <VChip
                class="tw-mr-1"
                size="small"
                :color="user?.active ? 'success' : 'error'"
                variant="tonal"
              >
                {{ user?.active ? $t('enabled') : $t('disabled') }}
              </VChip>
            </div>
          </div>
        </div>
        <div class="tw-flex tw-flex-col tw-gap-4 tw-justify-between">
          <div class="tw-flex tw-gap-2 tw-justify-end">
            <div v-if="user?.deletedAt">
              <DeletedAlert :data="user" />
            </div>
            <div class="tw-flex tw-gap-2">
              <VTooltip
                v-if="permission?.writePermission"
                :text="$t('edit')"
              >
                <template #activator="{ props }">
                  <div v-bind="props">
                    <VBtn
                      size="38"
                      :disabled="user?.deletedAt ? true : false"
                      :color="user?.deletedAt ? 'secondary' : 'primary'"
                      @click="navigateTo(`/users/form/${uuid}`)"
                    >
                      <VIcon
                        icon="tabler-edit"
                        size="22"
                      />
                    </VBtn>
                  </div>
                </template>
              </VTooltip>
            </div>
          </div>
          <div class=" tw-gap-2 tw-justify-end">
            <div class="tw-flex tw-justify-end tw-gap-1 tw-ml-2">
              <div class="tw-font-normal tw-flex-shrink-0">
                {{ $t('createdBy') }} :
              </div>
              <div>
                <span class="tw-font-normal">{{ displayOrFallback(user?.creator?.fullName || '') }}</span>
              </div>
            </div>
            <div class="tw-flex tw-justify-end tw-gap-1">
              <VTooltip :text="$t('lastLogin')">
                <template #activator="{ props }">
                  <VLabel>
                    <VIcon
                      v-bind="props"
                      icon="tabler-calendar"
                      size="22"
                      class="tw-mr-2"
                      variant="tonal"
                    />
                    {{ displayOrFallback(user?.lastLogin) }}
                  </VLabel>
                </template>
              </VTooltip>
              |
              <VTooltip :text="$t('timesLoginIn')">
                <template #activator="{ props }">
                  <VLabel>
                    <VIcon
                      v-bind="props"
                      icon="tabler-clock"
                      size="22"
                      class="tw-mr-2"
                      variant="tonal"
                    />
                    {{ displayOrFallback(user?.loginCount) }}
                  </VLabel>
                </template>
              </VTooltip>
            </div>
          </div>
        </div>
      </VCard>
      <AppTabs
        :tabItems="tabItems"
        :randomId="uuid"
        :entityData="user"
      >
        <template #middleColumnData>
          <VCard width="100%">
            <VCardText>
              <VRow class="tw-mb-2">
                <VTooltip :text="$t('dateCreated')">
                  <template #activator="{ props }">
                    <VLabel>
                      <VIcon
                        v-bind="props"
                        icon="tabler-calendar-plus"
                        size="22"
                        class="tw-mr-2"
                        variant="tonal"
                      />
                      {{ displayOrFallback(user?.createdAt) }}
                    </VLabel>
                  </template>
                </VTooltip>
              </VRow>
              <VRow class="tw-mb-2">
                <VTooltip :text="$t('dateModified')">
                  <template #activator="{ props }">
                    <VLabel>
                      <VIcon
                        v-bind="props"
                        icon="mdi-calendar-edit-outline"
                        size="22"
                        class="tw-mr-2"
                        variant="tonal"
                      />
                      {{ displayOrFallback(user?.updatedAt) }}
                    </VLabel>
                  </template>
                </VTooltip>
              </VRow>
            </VCardText>
          </VCard>
        </template>
      </AppTabs>
    </template>
  </div>
</template>
