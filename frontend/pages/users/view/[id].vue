<script setup lang="ts">
/**
 * Staff view — EQMS header + AppTabs (hash-linked) + info panel.
 * Links: list email → view, view edit → form, form save → view.
 */

const { t } = useI18n()

definePageMeta({
  navActiveLink: 'users',
  checkPermission: 'users.view',
})

useHead({
  title: t('userDetail') || 'Staff detail',
})

const { $toast } = useNuxtApp()
const { getUserViewById, userPasswordReset, loading } = useUserApiClient()
const route = useRoute()
const uuid = ref(String(route.params.id))
const user = ref<User | undefined>()
const tabItems = ref([] as AppTab[])
const resetOpen = ref(false)
const resetSaving = ref(false)
const newPassword = ref('')

const displayName = computed(() =>
  user.value?.fullName
  || user.value?.email
  || '—',
)

const roleLabel = computed(() =>
  user.value?.role?.roleName
  || user.value?.role?.name
  || null,
)

const createdAtLabel = computed(() => {
  const raw = (user.value as any)?.createdAt
  if (!raw)
    return null

  return new Date(raw).toLocaleString()
})

const lastLoginLabel = computed(() => {
  const raw = (user.value as any)?.lastLogin
  if (!raw)
    return '--'

  return new Date(raw).toLocaleString()
})

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

const openReset = () => {
  newPassword.value = ''
  resetOpen.value = true
}

const submitReset = async () => {
  const staffId = Number(user.value?.id || uuid.value)
  if (!staffId || String(newPassword.value).length < 8) {
    $toast.error('Password must be at least 8 characters.')

    return
  }

  resetSaving.value = true
  try {
    const response = await userPasswordReset({
      staff_id: staffId,
      new_password: newPassword.value,
    })

    if (response && (response as any).status !== false) {
      $toast.success((response as any).message || 'Password reset.')
      resetOpen.value = false
      newPassword.value = ''
    }
  }
  finally {
    resetSaving.value = false
  }
}

reloadData()

watch(() => route.params.id, (id) => {
  if (!id)
    return

  uuid.value = String(id)
  reloadData()
})
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
              {{ displayName }}
            </div>
            <div class="tw-text-base tw-font-semibold">
              <VChip size="small">
                {{ displayOrFallback(roleLabel) }}
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
            <VTooltip text="Reset password">
              <template #activator="{ props: tip }">
                <div v-bind="tip">
                  <VBtn
                    size="38"
                    color="warning"
                    variant="tonal"
                    @click="openReset"
                  >
                    <VIcon
                      icon="tabler-key"
                      size="22"
                    />
                  </VBtn>
                </div>
              </template>
            </VTooltip>
            <VTooltip :text="$t('edit')">
              <template #activator="{ props: tip }">
                <div v-bind="tip">
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
          <div class="tw-flex tw-justify-end tw-gap-1">
            <VTooltip :text="$t('lastLogin') || 'Last login'">
              <template #activator="{ props: tip }">
                <VLabel>
                  <VIcon
                    v-bind="tip"
                    icon="tabler-calendar"
                    size="22"
                    class="tw-mr-2"
                  />
                  {{ lastLoginLabel }}
                </VLabel>
              </template>
            </VTooltip>
          </div>
        </div>
      </VCard>

      <AppTabs
        v-if="tabItems.length"
        :tab-items="tabItems"
        :random-id="uuid"
        :entity-data="user"
      >
        <template #middleColumnData>
          <VCard width="100%">
            <VCardText class="pl-2 pt-2">
              <span class="d-flex mt-2 tw-items-center">
                <VTooltip :text="$t('dateCreated') || 'Created'">
                  <template #activator="{ props: tip }">
                    <VIcon
                      v-bind="tip"
                      size="25"
                      icon="tabler-calendar-plus"
                      class="me-1"
                    />
                  </template>
                </VTooltip>
                <span class="text-body-1">
                  <VLabel class="tw-font-semibold">
                    {{ displayOrFallback(createdAtLabel) }}
                  </VLabel>
                </span>
              </span>
            </VCardText>
          </VCard>
        </template>
      </AppTabs>
    </template>

    <VDialog
      v-model="resetOpen"
      max-width="420"
      persistent
    >
      <VCard>
        <VCardTitle>Reset password</VCardTitle>
        <VCardText>
          <AppTextField
            v-model="newPassword"
            type="password"
            label="New password"
            autocomplete="new-password"
            hint="Min 8 characters"
            persistent-hint
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="resetOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="resetSaving"
            @click="submitReset"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
