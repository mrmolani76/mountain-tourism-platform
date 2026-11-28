<script setup lang="ts">
import type { SaasDashboardActivity } from '@/composables/api-clients/useSaasApiClient'

definePageMeta({
  layout: 'saas',
  title: 'Platform overview',
})
useHead({ title: 'Platform overview' })

const api = useSaasApiClient()
const loading = ref(true)
const loaded = ref(false)
const error = ref('')
const counts = ref({ total: 0, active: 0, inactive: 0 })
const recentActivity = ref<SaasDashboardActivity[]>([])

const statCards = computed(() => [
  { title: 'All complexes', value: counts.value.total, icon: 'tabler-building-community', color: 'primary', to: '/saas/tenants' },
  { title: 'Active', value: counts.value.active, icon: 'tabler-circle-check', color: 'success', to: '/saas/tenants?status=active' },
  { title: 'Inactive', value: counts.value.inactive, icon: 'tabler-alert-circle', color: 'warning', to: '/saas/tenants?status=inactive' },
])

const eventLabel = (eventType: string) => ({
  tenant_created: 'Complex created',
  tenant_activated: 'Complex activated',
  tenant_deactivated: 'Complex deactivated',
  owner_created: 'Owner account created',
}[eventType] || 'Platform activity')

const load = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.getDashboard()
    if (!response) {
      error.value = 'Could not load the platform overview. Retry the request.'
      return
    }

    counts.value = {
      total: response.total,
      active: response.active,
      inactive: response.inactive,
    }
    recentActivity.value = response.recent_activity || []
    loaded.value = true
  }
  finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-6">
      <div>
        <h1 class="text-h4 mb-1">
          Platform overview
        </h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Complex registry, status, and onboarding at a glance.
        </p>
      </div>
      <VBtn
        to="/saas/tenants/create"
        prepend-icon="tabler-plus"
      >
        Add complex
      </VBtn>
    </div>

    <VAlert
      v-if="error"
      type="error"
      variant="tonal"
      class="mb-5"
    >
      {{ error }}
      <template #append>
        <VBtn
          variant="text"
          :disabled="loading"
          @click="load"
        >
          Retry
        </VBtn>
      </template>
    </VAlert>

    <VRow>
      <VCol
        v-for="card in statCards"
        :key="card.title"
        cols="12"
        sm="6"
        lg="4"
      >
        <VCard
          :to="card.to"
          class="saas-kpi-card h-100"
          variant="outlined"
          :loading="loading && !loaded"
          hover
        >
          <VCardText class="d-flex align-center ga-3">
            <div
              class="saas-kpi-icon d-flex align-center justify-center"
              :class="`bg-${card.color}`"
            >
              <VIcon
                :icon="card.icon"
                color="white"
                size="22"
              />
            </div>
            <div>
              <div class="text-h5 font-weight-bold">
                {{ !loaded ? '—' : card.value }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ card.title }}
              </div>
            </div>
            <VSpacer />
            <VIcon
              icon="tabler-chevron-right"
              size="18"
              class="text-medium-emphasis"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mt-2">
      <VCol
        cols="12"
        md="7"
      >
        <VCard
          variant="outlined"
          class="h-100"
        >
          <VCardItem>
            <VCardTitle>Recent activity</VCardTitle>
            <template #append>
              <VBtn
                size="small"
                variant="text"
                to="/saas/tenants"
              >
                View complexes
              </VBtn>
            </template>
          </VCardItem>
          <VCardText>
            <VSkeletonLoader
              v-if="loading && !loaded"
              type="list-item-two-line@4"
            />
            <VList
              v-else-if="recentActivity.length"
              density="compact"
              class="py-0"
            >
              <VListItem
                v-for="event in recentActivity"
                :key="event.id"
                :title="eventLabel(event.event_type)"
                :subtitle="`${event.tenant_name || 'Complex'} · ${event.actor_email} · ${new Date(event.created_at).toLocaleString()}`"
              >
                <template #append>
                  <VBtn
                    v-if="event.tenant_uuid"
                    icon="tabler-eye"
                    size="small"
                    variant="text"
                    :to="`/saas/tenants/${event.tenant_uuid}`"
                    :aria-label="`View ${event.tenant_name || 'complex'}`"
                  />
                </template>
              </VListItem>
            </VList>
            <VAlert
              v-else-if="loaded"
              type="info"
              density="compact"
              variant="tonal"
            >
              No platform activity is recorded yet.
            </VAlert>
            <VAlert
              v-else
              type="warning"
              density="compact"
              variant="tonal"
            >
              Activity could not be loaded.
            </VAlert>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="5"
      >
        <VCard
          variant="outlined"
          class="h-100"
        >
          <VCardItem>
            <VCardTitle>Tenant onboarding</VCardTitle>
          </VCardItem>
          <VCardText>
            <p class="text-body-2 text-medium-emphasis">
              Review database, Core migration, and Owner readiness for each complex.
            </p>
            <VBtn
              to="/saas/tenants"
              variant="tonal"
              append-icon="tabler-arrow-right"
            >
              Open complexes
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.saas-kpi-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.saas-kpi-card:hover {
  transform: translateY(-2px);
}

.saas-kpi-icon {
  inline-size: 44px;
  block-size: 44px;
  flex-shrink: 0;
  border-radius: 12px;
}

.saas-kpi-icon.bg-primary {
  background: rgb(var(--v-theme-primary));
}

.saas-kpi-icon.bg-success {
  background: rgb(var(--v-theme-success));
}

.saas-kpi-icon.bg-warning {
  background: rgb(var(--v-theme-warning));
}
</style>
