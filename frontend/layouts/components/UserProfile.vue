<script setup lang="ts">
const store = authStore()

const user = computed(() => store.getUser)
const userImage = computed(() => store.getUserImage)

const userLogout = async () => {
  await store.logoutUser()
  await navigateTo('/login', { replace: true })
}
</script>

<template>
  <div class="tw-flex tw-gap-3 tw-align-middle cursor-pointer">
    <VBadge
      dot
      location="bottom right"
      color="success"
    >
      <VAvatar
        variant="tonal"
        icon="tabler-user"
        class="tw-mt-1"
        :image="userImage || undefined"
      />
    </VBadge>
    <div class="">
      <div class="font-weight-semibold">
        {{ user?.prefixedUsername || user?.email || 'Staff' }}
      </div>
      <div>{{ user?.role?.roleName || 'Staff' }}</div>
    </div>

    <!-- SECTION Menu -->
    <VMenu
      activator="parent"
      width="230"
      location="bottom end"
      offset="14px"
    >
      <VList>
        <!-- Profile -->
        <VListItem :to="{ path: '/user-profile' }">
          <template #prepend>
            <VIcon
              class="me-2"
              icon="tabler-user"
              size="22"
            />
          </template>

          <VListItemTitle>{{ $t('profile') }}</VListItemTitle>
        </VListItem>
        <VDivider
          :thickness="2"
          class="border-opacity-75 tw-my-2"
        />

        <!-- Logout -->
        <VListItem @click="userLogout">
          <template #prepend>
            <VIcon
              class="me-2"
              icon="tabler-logout"
              size="22"
            />
          </template>

          <VListItemTitle>{{ $t('logout') }}</VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
    <!-- !SECTION -->
  </div>
</template>
