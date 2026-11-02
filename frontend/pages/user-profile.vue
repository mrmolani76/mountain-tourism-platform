<script setup lang="ts">
const store = authStore()

const user = computed(() => store.getUser)
const userImage = computed(() => store.getUserImage)

const statusMeta = computed(() => {
  if (user.value?.active)
    return { color: 'success', text: 'Active' }

  return { color: 'error', text: 'Inactive' }
})

const logout = async () => {
  await store.logoutUser()
  await navigateTo('/login', { replace: true })
}

useHead({
  title: 'Profile',
})
</script>

<template>
  <div class="mb-6">
    <h1 class="text-h4 mb-1">
      Profile
    </h1>
    <p class="text-medium-emphasis mb-0">
      Your staff account for this tenant.
    </p>
  </div>

  <VRow>
    <VCol
      cols="12"
      md="5"
      lg="4"
    >
      <VCard>
        <VCardText class="text-center py-8">
          <VAvatar
            v-if="userImage"
            rounded
            :size="96"
          >
            <VImg :src="userImage" />
          </VAvatar>
          <VAvatar
            v-else
            rounded
            variant="tonal"
            color="primary"
            icon="tabler-user"
            :size="96"
          />

          <h2 class="text-h5 mt-4">
            {{ user?.prefixedUsername || 'Staff' }}
          </h2>
          <p class="text-body-2 text-medium-emphasis mb-3">
            {{ user?.email }}
          </p>

          <VChip
            color="primary"
            variant="tonal"
            class="text-capitalize"
          >
            {{ user?.role?.roleName || 'Staff' }}
          </VChip>
          <VChip
            :color="statusMeta.color"
            variant="tonal"
            class="text-capitalize ms-2"
          >
            {{ statusMeta.text }}
          </VChip>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      md="7"
      lg="8"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Account details</VCardTitle>
          <VCardSubtitle>
            Read-only values from the Django staff session.
          </VCardSubtitle>
        </VCardItem>

        <VCardText>
          <VList lines="two">
            <VListItem>
              <template #prepend>
                <VIcon
                  icon="tabler-mail"
                  class="me-3"
                />
              </template>
              <VListItemSubtitle>Email</VListItemSubtitle>
              <VListItemTitle>{{ user?.email || 'Not available' }}</VListItemTitle>
            </VListItem>

            <VListItem>
              <template #prepend>
                <VIcon
                  icon="tabler-badge"
                  class="me-3"
                />
              </template>
              <VListItemSubtitle>Role</VListItemSubtitle>
              <VListItemTitle>{{ user?.role?.roleName || 'Not available' }}</VListItemTitle>
            </VListItem>

            <VListItem>
              <template #prepend>
                <VIcon
                  icon="tabler-code"
                  class="me-3"
                />
              </template>
              <VListItemSubtitle>Role code</VListItemSubtitle>
              <VListItemTitle>{{ user?.roleCode || 'Not available' }}</VListItemTitle>
            </VListItem>

            <VListItem>
              <template #prepend>
                <VIcon
                  icon="tabler-building"
                  class="me-3"
                />
              </template>
              <VListItemSubtitle>Tenant</VListItemSubtitle>
              <VListItemTitle>{{ user?.tenantSlug || 'Not available' }}</VListItemTitle>
            </VListItem>

            <VListItem>
              <template #prepend>
                <VIcon
                  icon="tabler-id"
                  class="me-3"
                />
              </template>
              <VListItemSubtitle>Staff ID</VListItemSubtitle>
              <VListItemTitle>{{ user?.id || 'Not available' }}</VListItemTitle>
            </VListItem>
          </VList>

          <VAlert
            type="info"
            variant="tonal"
            icon="tabler-lock"
            class="mt-4"
          >
            Profile edits and password resets are handled by an owner or manager.
            There is no self-service password reset in this release.
          </VAlert>

          <div class="d-flex justify-end mt-4">
            <VBtn
              color="error"
              variant="tonal"
              prepend-icon="tabler-logout"
              @click="logout"
            >
              Sign out
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
