<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'
import type { SaasActivity, SaasReadiness, SaasTenant } from '@/composables/api-clients/useSaasApiClient'

definePageMeta({
  layout: 'saas',
  title: 'Complex details',
})

const route = useRoute()
const api = useSaasApiClient()
const tenantUuid = String(route.params.uuid)
const loading = ref(true)
const actionLoading = ref(false)
const error = ref('')
const tenant = ref<SaasTenant | null>(null)
const readiness = ref<SaasReadiness | null>(null)
const activity = ref<SaasActivity[]>([])
const activityLoaded = ref(false)
const activityError = ref('')
const confirmOpen = ref(false)
const ownerEmail = ref('')
const ownerFormRef = ref<VForm>()
const ownerLoading = ref(false)
const ownerError = ref('')
const ownerTemporaryPassword = ref('')
const ownerPasswordCopyError = ref('')
const ownerEmailDeliveryStatus = ref<'not_configured' | 'sent' | 'failed' | ''>('')

const readinessRows = computed(() => {
  if (!readiness.value)
    return []

  return [
    { title: 'Central registry', detail: 'Complex record exists in the central registry.', item: readiness.value.registry },
    { title: 'Primary domain', detail: 'A primary host is registered for this complex.', item: readiness.value.primary_domain },
    { title: 'Tenant database', detail: 'The tenant database can be reached from the central service.', item: readiness.value.tenant_database },
    { title: 'Core migrations', detail: 'Required Core tables are present on the tenant database.', item: readiness.value.tenant_migrations },
    { title: 'Owner account', detail: 'An active Owner staff account is available.', item: readiness.value.owner_account },
  ]
})

const load = async () => {
  loading.value = true
  error.value = ''

  try {
    const [tenantResponse, readinessResponse, activityResponse] = await Promise.all([
      api.getTenant(tenantUuid),
      api.getReadiness(tenantUuid),
      api.getActivity(tenantUuid),
    ])

    if (!tenantResponse || !readinessResponse) {
      error.value = 'Complex details are temporarily unavailable. Retry the request.'
      return
    }

    tenant.value = tenantResponse
    readiness.value = readinessResponse
    activity.value = activityResponse || []
    activityLoaded.value = Boolean(activityResponse)
    activityError.value = activityResponse ? '' : 'Lifecycle activity could not be loaded.'
  }
  finally {
    loading.value = false
  }
}

const copyOwnerTemporaryPassword = async () => {
  try {
    await navigator.clipboard.writeText(ownerTemporaryPassword.value)
    ownerPasswordCopyError.value = ''
  }
  catch {
    ownerPasswordCopyError.value = 'Clipboard access is unavailable. Select and copy the password manually.'
  }
}

const updateStatus = async () => {
  if (!tenant.value || actionLoading.value)
    return

  actionLoading.value = true
  error.value = ''

  try {
    const updated = await api.setTenantActive(tenantUuid, !tenant.value.is_active)
    if (!updated) {
      error.value = 'Complex status could not be changed.'
      return
    }

    confirmOpen.value = false
    await load()
  }
  finally {
    actionLoading.value = false
  }
}

const createOwnerAccount = async () => {
  const validation = await ownerFormRef.value?.validate()
  if (
    !validation?.valid
    || !readiness.value?.tenant_database.ready
    || !readiness.value.tenant_migrations.ready
    || ownerLoading.value
  ) {
    return
  }

  ownerLoading.value = true
  ownerError.value = ''
  ownerTemporaryPassword.value = ''
  ownerPasswordCopyError.value = ''
  ownerEmailDeliveryStatus.value = ''

  try {
    const result = await api.createOwner(tenantUuid, {
      owner_email: ownerEmail.value.trim(),
    })

    if (!result?.owner_created) {
      ownerError.value = 'Owner account could not be created. Confirm the email is unused and retry.'
      return
    }

    ownerTemporaryPassword.value = result.owner_temporary_password
    ownerEmailDeliveryStatus.value = result.owner_email_delivery_status
    ownerEmail.value = ''
    await load()
  }
  finally {
    ownerLoading.value = false
  }
}

const eventLabel = (eventType: string) => ({
  tenant_created: 'Complex created',
  tenant_activated: 'Complex activated',
  tenant_deactivated: 'Complex deactivated',
  owner_created: 'Owner account created',
}[eventType] || 'Complex updated')

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
      <div class="d-flex align-center ga-3">
        <VBtn
          icon="tabler-arrow-left"
          variant="text"
          to="/saas/tenants"
          aria-label="Back to complexes"
        />
        <div>
          <h1 class="text-h4 mb-1">
            {{ tenant?.name || 'Complex details' }}
          </h1>
          <p class="text-body-2 text-medium-emphasis mb-0">
            Tenant registry and onboarding readiness.
          </p>
        </div>
      </div>
      <div
        v-if="tenant"
        class="d-flex flex-wrap ga-2"
      >
        <VBtn
          variant="tonal"
          prepend-icon="tabler-edit"
          :to="`/saas/tenants/${tenant.uuid}/edit`"
        >
          Edit
        </VBtn>
        <VBtn
          :color="tenant.is_active ? 'warning' : 'success'"
          :prepend-icon="tenant.is_active ? 'tabler-player-pause' : 'tabler-player-play'"
          :disabled="actionLoading"
          @click="confirmOpen = true"
        >
          {{ tenant.is_active ? 'Deactivate' : 'Activate' }}
        </VBtn>
      </div>
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
          @click="load"
        >
          Retry
        </VBtn>
      </template>
    </VAlert>

    <VSkeletonLoader
      v-if="loading && !tenant"
      type="article, table"
    />

    <template v-else-if="tenant">
      <VRow>
        <VCol
          cols="12"
          lg="5"
        >
          <VCard
            variant="outlined"
            class="h-100"
          >
            <VCardTitle class="d-flex align-center justify-space-between">
              Complex information
              <VChip
                size="small"
                :color="tenant.is_active ? 'success' : 'warning'"
                variant="tonal"
              >
                {{ tenant.is_active ? 'Active' : 'Inactive' }}
              </VChip>
            </VCardTitle>
            <VCardText>
              <VList density="compact">
                <VListItem title="Slug">
                  <template #append>
                    <span class="text-body-2">{{ tenant.slug }}</span>
                  </template>
                </VListItem>
                <VListItem title="Primary domain">
                  <template #append>
                    <span class="text-body-2">
                      {{ tenant.primary_domain || tenant.domains[0] || 'Not set' }}
                    </span>
                  </template>
                </VListItem>
                <VListItem title="Created">
                  <template #append>
                    <span class="text-body-2">
                      {{ tenant.created_at ? new Date(tenant.created_at).toLocaleDateString() : '—' }}
                    </span>
                  </template>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>
        </VCol>

        <VCol
          cols="12"
          lg="7"
        >
          <VCard variant="outlined">
            <VCardTitle class="d-flex flex-wrap align-center justify-space-between ga-2">
              Onboarding readiness
              <VChip
                v-if="readiness"
                size="small"
                :color="readiness.ready ? 'success' : 'warning'"
                variant="tonal"
              >
                {{ readiness.ready ? 'Ready' : 'Action needed' }}
              </VChip>
            </VCardTitle>
            <VCardText>
              <VSkeletonLoader
                v-if="loading && !readiness"
                type="list-item-two-line@5"
              />
              <VList
                v-else
                density="compact"
              >
                <VListItem
                  v-for="row in readinessRows"
                  :key="row.title"
                  :title="row.title"
                  :subtitle="row.item.ready ? row.detail : row.item.next_step || row.detail"
                >
                  <template #prepend>
                    <VIcon
                      :icon="row.item.ready ? 'tabler-circle-check' : 'tabler-alert-circle'"
                      :color="row.item.ready ? 'success' : 'warning'"
                      class="me-2"
                    />
                  </template>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12">
          <VCard variant="outlined">
            <VCardTitle>
              Recent activity
            </VCardTitle>
            <VCardText>
              <VAlert
                v-if="activityError"
                type="warning"
                variant="tonal"
                class="mb-3"
              >
                {{ activityError }}
              </VAlert>
              <VAlert
                v-if="activityLoaded && !activity.length"
                type="info"
                variant="tonal"
                class="mb-0"
              >
                No lifecycle activity is recorded yet.
              </VAlert>
              <VList
                v-else
                density="compact"
              >
                <VListItem
                  v-for="event in activity"
                  :key="event.id"
                  :title="eventLabel(event.event_type)"
                  :subtitle="`${event.actor_email} · ${new Date(event.created_at).toLocaleString()}`"
                />
              </VList>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VAlert
        v-if="ownerTemporaryPassword"
        type="success"
        variant="tonal"
        class="mt-4"
      >
        <div class="font-weight-medium">
          Owner account created.
          {{ ownerEmailDeliveryStatus === 'sent'
            ? 'Temporary credentials were emailed.'
            : `Email delivery status: ${ownerEmailDeliveryStatus}.` }}
          The password must be changed at first login.
        </div>
        <VTextField
          :model-value="ownerTemporaryPassword"
          label="One-time temporary Owner password"
          readonly
          density="compact"
          hide-details
          class="mt-3 mb-2"
        />
        <VBtn
          variant="tonal"
          prepend-icon="tabler-copy"
          @click="copyOwnerTemporaryPassword"
        >
          Copy password
        </VBtn>
        <div
          v-if="ownerPasswordCopyError"
          class="text-caption text-error mt-2"
        >
          {{ ownerPasswordCopyError }}
        </div>
      </VAlert>

      <VCard
        v-if="readiness && !readiness.owner_account.ready"
        variant="outlined"
        class="mt-4"
      >
        <VCardTitle>
          Create Owner account
        </VCardTitle>
        <VCardText>
          <p class="text-body-2 text-medium-emphasis">
            This creates a new Owner only. Existing staff accounts are never promoted or overwritten.
          </p>
          <VAlert
            v-if="ownerError"
            type="error"
            variant="tonal"
            class="mb-4"
          >
            {{ ownerError }}
          </VAlert>
          <VAlert
            v-if="!readiness.tenant_database.ready || !readiness.tenant_migrations.ready"
            type="info"
            variant="tonal"
            class="mb-4"
          >
            Provision the tenant database and run the approved Core migrations before creating an Owner.
          </VAlert>
          <VForm
            ref="ownerFormRef"
            @submit.prevent="createOwnerAccount"
          >
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="ownerEmail"
                  label="Owner email"
                  type="email"
                  autocomplete="email"
                  :rules="[requiredValidator, emailValidator]"
                  :disabled="ownerLoading || !readiness.tenant_database.ready || !readiness.tenant_migrations.ready"
                />
              </VCol>
            </VRow>
            <div class="d-flex justify-end">
              <VBtn
                type="submit"
                :loading="ownerLoading"
                :disabled="ownerLoading || !readiness.tenant_database.ready || !readiness.tenant_migrations.ready"
              >
                Create Owner
              </VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </template>

    <VDialog
      v-model="confirmOpen"
      max-width="460"
    >
      <VCard>
        <VCardTitle>
          {{ tenant?.is_active ? 'Deactivate complex?' : 'Activate complex?' }}
        </VCardTitle>
        <VCardText>
          {{ tenant?.is_active
            ? 'Staff on this complex will lose platform access. Tenant data will not be deleted.'
            : 'Staff on this complex will be able to access the platform again.' }}
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            :disabled="actionLoading"
            @click="confirmOpen = false"
          >
            Cancel
          </VBtn>
          <VBtn
            :color="tenant?.is_active ? 'warning' : 'success'"
            :loading="actionLoading"
            :disabled="actionLoading"
            @click="updateStatus"
          >
            Confirm
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
