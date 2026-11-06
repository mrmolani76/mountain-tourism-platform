<script setup lang="ts">
useHead({
  title: 'Dashboard',
})

const store = authStore()
const { get } = useApiClient()

const user = computed(() => store.getUser)
const health = ref<'checking' | 'online' | 'offline'>('checking')

const healthMeta = computed(() => {
  if (health.value === 'online')
    return { color: 'success', text: 'API online', icon: 'tabler-circle-check' }

  if (health.value === 'offline')
    return { color: 'error', text: 'API offline', icon: 'tabler-alert-circle' }

  return { color: 'warning', text: 'Checking API', icon: 'tabler-loader' }
})

async function checkHealth() {
  health.value = 'checking'

  const response = await get<{ status: string; service: string }>('/health/')

  health.value = response?.status === 'ok' ? 'online' : 'offline'
}

onMounted(checkHealth)
</script>

<template>
  <div class="d-flex flex-wrap align-center justify-space-between gap-3">
    <div>
      <h1 class="text-h4 mb-1">
        Dashboard
      </h1>
      <p class="text-medium-emphasis mb-0">
        Mountain complex operations at a glance.
      </p>
    </div>

    <VBtn
      variant="tonal"
      prepend-icon="tabler-refresh"
      :loading="health === 'checking'"
      @click="checkHealth"
    >
      Check API
    </VBtn>
  </div>

  <VRow class="mt-6">
    <VCol
      cols="12"
      sm="6"
      lg="3"
    >
      <VCard class="h-100">
        <VCardText>
          <div class="d-flex align-center justify-space-between">
            <span class="text-sm text-medium-emphasis">Signed in as</span>
            <VIcon
              icon="tabler-user-circle"
              color="primary"
            />
          </div>
          <div class="text-h6 mt-3">
            {{ user?.prefixedUsername || 'Staff' }}
          </div>
          <div class="text-body-2 text-medium-emphasis">
            {{ user?.email }}
          </div>
          <VChip
            class="mt-3 text-capitalize"
            size="small"
            color="primary"
            variant="tonal"
          >
            {{ user?.role?.roleName || 'Staff' }}
          </VChip>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      sm="6"
      lg="3"
    >
      <VCard class="h-100">
        <VCardText>
          <div class="d-flex align-center justify-space-between">
            <span class="text-sm text-medium-emphasis">Tenant</span>
            <VIcon
              icon="tabler-building-community"
              color="primary"
            />
          </div>
          <div class="text-h6 mt-3">
            {{ user?.tenantSlug || 'Central' }}
          </div>
          <div class="text-body-2 text-medium-emphasis">
            Shared database, tenant scoped rows
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      sm="6"
      lg="3"
    >
      <VCard class="h-100">
        <VCardText>
          <div class="d-flex align-center justify-space-between">
            <span class="text-sm text-medium-emphasis">Backend</span>
            <VIcon
              icon="tabler-server-2"
              color="primary"
            />
          </div>
          <VChip
            class="mt-3"
            size="small"
            :color="healthMeta.color"
            :prepend-icon="healthMeta.icon"
          >
            {{ healthMeta.text }}
          </VChip>
          <div class="text-body-2 text-medium-emphasis mt-3">
            Django REST API
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      sm="6"
      lg="3"
    >
      <VCard class="h-100">
        <VCardText>
          <div class="d-flex align-center justify-space-between">
            <span class="text-sm text-medium-emphasis">Modules</span>
            <VIcon
              icon="tabler-apps"
              color="primary"
            />
          </div>
          <div class="text-h6 mt-3">
            {{ appModules.length }} modules
          </div>
          <div class="text-body-2 text-medium-emphasis">
            APIs live, screens next
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <div class="d-flex align-center justify-space-between mt-8 mb-4">
    <div>
      <h2 class="text-h5 mb-1">
        Operations
      </h2>
      <p class="text-medium-emphasis mb-0">
        Open a module to see its API scope.
      </p>
    </div>
  </div>

  <VRow>
    <VCol
      v-for="module in appModules"
      :key="module.key"
      cols="12"
      sm="6"
      lg="4"
    >
      <VCard
        class="h-100"
        :to="{ path: `/${module.key}` }"
        hover
      >
        <VCardItem>
          <template #prepend>
            <VAvatar
              color="primary"
              variant="tonal"
            >
              <VIcon :icon="module.icon" />
            </VAvatar>
          </template>

          <VCardTitle>{{ module.title }}</VCardTitle>
          <VCardSubtitle class="text-capitalize">
            {{ module.key }}
          </VCardSubtitle>
        </VCardItem>

        <VCardText class="text-body-2">
          {{ module.description }}
        </VCardText>

        <VCardText class="pt-0">
          <VChip
            size="small"
            variant="tonal"
            prepend-icon="tabler-api"
          >
            {{ module.endpoint }}
          </VChip>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <VAlert
    type="info"
    variant="tonal"
    icon="tabler-lock"
    class="mt-6"
  >
    Charges and payments are append-only. Correct a mistake with a void that records
    the reason, user and timestamp. Never edit or delete a money row.
  </VAlert>
</template>
