<script setup lang="ts">
import { useDisplay } from 'vuetify'

const { user, logout } = useSaasAuth()
const route = useRoute()
const display = useDisplay()
const drawer = ref(false)

const navigationGroups = [
  {
    title: 'Overview',
    items: [
      { title: 'Dashboard', to: '/saas', icon: 'tabler-chart-line' },
    ],
  },
  {
    title: 'Tenant management',
    items: [
      { title: 'Complexes', to: '/saas/tenants', icon: 'tabler-building-community' },
    ],
  },
]

const pageTitle = computed(() => {
  const title = route.meta.title

  if (typeof title === 'string' && title)
    return title

  return route.path.startsWith('/saas/tenants') ? 'Complexes' : 'Platform overview'
})

const userInitial = computed(() => user.value?.email?.charAt(0).toUpperCase() || 'A')

const closeDrawerOnMobile = () => {
  if (!display.mdAndUp.value)
    drawer.value = false
}

const signOut = async () => {
  await logout()
  await navigateTo('/saas/login', { replace: true })
}

watch(
  () => display.mdAndUp.value,
  isDesktop => {
    drawer.value = isDesktop
  },
  { immediate: true },
)
</script>

<template>
  <VLayout class="saas-layout">
    <VNavigationDrawer
      v-model="drawer"
      :permanent="display.mdAndUp.value"
      :temporary="!display.mdAndUp.value"
      width="260"
      class="saas-sidebar"
    >
      <div class="d-flex align-center ga-3 px-5 py-5">
        <div class="saas-brand-mark d-flex align-center justify-center">
          <VIcon
            icon="tabler-mountain"
            color="primary"
            size="22"
          />
        </div>
        <div>
          <div class="text-subtitle-1 font-weight-bold text-primary">
            Mountain Tourism
          </div>
          <div class="text-caption text-medium-emphasis">
            Platform admin
          </div>
        </div>
      </div>

      <VDivider />

      <VList
        nav
        class="px-3 py-3"
      >
        <template
          v-for="group in navigationGroups"
          :key="group.title"
        >
          <VListSubheader class="text-uppercase text-caption font-weight-bold">
            {{ group.title }}
          </VListSubheader>
          <VListItem
            v-for="item in group.items"
            :key="item.to"
            :to="item.to"
            :prepend-icon="item.icon"
            :title="item.title"
            :active="route.path === item.to || (item.to !== '/saas' && route.path.startsWith(`${item.to}/`))"
            active-class="saas-nav-active"
            rounded="lg"
            class="mb-1"
            @click="closeDrawerOnMobile"
          />
        </template>
      </VList>
    </VNavigationDrawer>

    <div class="saas-content">
      <VAppBar
        flat
        border="bottom"
        height="64"
        class="px-2"
      >
        <VBtn
          v-if="!display.mdAndUp.value"
          icon="tabler-menu-2"
          variant="text"
          aria-label="Open navigation"
          @click="drawer = !drawer"
        />
        <VAppBarTitle class="text-subtitle-1 font-weight-medium">
          {{ pageTitle }}
        </VAppBarTitle>
        <VSpacer />
        <VMenu
          width="240"
          location="bottom end"
        >
          <template #activator="{ props }">
            <VBtn
              v-bind="props"
              icon
              variant="text"
              aria-label="Open account menu"
              class="me-2"
            >
              <VAvatar
                color="primary"
                variant="tonal"
                size="36"
              >
                {{ userInitial }}
              </VAvatar>
            </VBtn>
          </template>
          <VList>
            <VListItem
              :title="user?.email || 'Super Admin'"
              subtitle="Platform administrator"
            />
            <VDivider class="my-1" />
            <VListItem
              title="Sign out"
              prepend-icon="tabler-logout"
              @click="signOut"
            />
          </VList>
        </VMenu>
      </VAppBar>

      <VMain class="saas-main">
        <VContainer
          fluid
          class="pa-4 pa-md-6"
        >
          <slot />
        </VContainer>
      </VMain>
    </div>
  </VLayout>
</template>

<style scoped>
.saas-layout {
  min-block-size: 100vh;
}

.saas-brand-mark {
  inline-size: 36px;
  block-size: 36px;
  flex-shrink: 0;
  border-radius: 10px;
  background: rgba(var(--v-theme-primary), 0.12);
}

.saas-content {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-inline-size: 0;
}

.saas-main {
  flex: 1;
  background-color: rgb(var(--v-theme-background));
}

.saas-nav-active {
  font-weight: 600;
}
</style>
