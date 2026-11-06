<script setup lang="ts">
/**
 * Staff view — header, info tab, skeleton loading.
 */

const { t } = useI18n()

definePageMeta({
  navActiveLink: 'users',
  checkPermission: 'users.view',
})

useHead({
  title: t('userDetail') || 'Staff detail',
})

const { getUserViewById, loading } = useUserApiClient()
const route = useRoute()
const uuid = ref(String(route.params.id))
const user = ref<User | undefined>()
const tabItems = ref([] as AppTab[])

const reloadData = async () => {
  const response = await getUserViewById(uuid.value)

  user.value = response as User | undefined
  tabItems.value = [
    {
      icon: 'tabler-info-circle',
      tab: 'userInfo',
      title: t('info'),
      pinnedTab: true,
      component: markRaw(resolveComponent('UserViewInfo') as object),
    },
  ]
}

reloadData()
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
            variant="tonal"
            icon="tabler-user"
            class="tw-mt-1"
            rounded
            :size="100"
          />
          <div>
            <div class="tw-text-2xl tw-font-bold tw-mb-2">
              {{ user?.fullName || user?.email }}
            </div>
            <div class="tw-text-base tw-font-semibold">
              <VChip size="small">
                {{ displayOrFallback(user?.role?.roleName) }}
              </VChip>
              |
              <VChip
                class="tw-mr-1"
                size="small"
                :color="user?.active ? 'success' : 'error'"
                variant="tonal"
              >
                {{ user?.active ? ($t('enabled') || 'Enabled') : ($t('disabled') || 'Disabled') }}
              </VChip>
            </div>
          </div>
        </div>

        <div class="tw-flex tw-flex-col tw-gap-4 tw-justify-between">
          <div class="tw-flex tw-gap-2 tw-justify-end">
            <VTooltip :text="$t('edit')">
              <template #activator="{ props }">
                <div v-bind="props">
                  <VBtn
                    size="38"
                    color="primary"
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
            <VBtn
              size="38"
              variant="tonal"
              color="secondary"
              @click="navigateTo('/users')"
            >
              <VIcon
                icon="tabler-arrow-left"
                size="22"
              />
            </VBtn>
          </div>
        </div>
      </VCard>

      <AppTabs
        v-if="tabItems.length"
        :tab-items="tabItems"
        :random-id="uuid"
        :entity-data="user"
      />

      <UserViewInfo
        v-else
        :entity-data="user"
      />
    </template>
  </div>
</template>
