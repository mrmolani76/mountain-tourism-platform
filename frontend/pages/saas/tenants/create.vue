<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'
import type { SaasTenant } from '@/composables/api-clients/useSaasApiClient'

definePageMeta({
  layout: 'saas',
  title: 'Add complex',
})
useHead({ title: 'Add complex' })

const api = useSaasApiClient()
const formRef = ref<VForm>()
const saving = ref(false)
const tenant = ref<SaasTenant | null>(null)
const temporaryPassword = ref('')
const ownerEmailDeliveryStatus = ref<'not_requested' | 'not_sent' | 'not_configured' | 'sent' | 'failed'>('not_requested')
const passwordCopyError = ref('')
const error = ref('')
const form = ref({
  name: '',
  slug: '',
  base_domain: 'mtmp.local',
  owner_email: '',
})

const domainRule = (value: string) =>
  !value || /^(?=.{1,253}$)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i.test(value) || 'Use a valid domain suffix.'

const slugRule = (value: string) =>
  (/^[a-z0-9-]{1,57}$/i.test(value) || 'Use 1 to 57 letters, numbers, and hyphens.')

const ownerEmailRule = (value: string) =>
  !value || emailValidator(value) || 'Enter a valid email.'

const save = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid || saving.value)
    return

  saving.value = true
  error.value = ''

  try {
    const body = {
      name: form.value.name.trim(),
      slug: form.value.slug.trim().toLowerCase(),
      base_domain: form.value.base_domain.trim() || undefined,
      owner_email: form.value.owner_email.trim() || undefined,
    }

    const response = await api.createTenant(body)
    if (response) {
      const { owner_temporary_password, ...safeTenant } = response
      temporaryPassword.value = owner_temporary_password || ''
      ownerEmailDeliveryStatus.value = response.owner_email_delivery_status
      tenant.value = safeTenant
    }

    if (!tenant.value)
      error.value = 'Complex creation failed. Check the form and retry.'
  }
  catch {
    error.value = 'Complex could not be created. Check the slug and provisioning status, then retry.'
  }
  finally {
    saving.value = false
  }
}

const copyTemporaryPassword = async () => {
  try {
    await navigator.clipboard.writeText(temporaryPassword.value)
    passwordCopyError.value = ''
  }
  catch {
    passwordCopyError.value = 'Clipboard access is unavailable. Select and copy the password manually.'
  }
}
</script>

<template>
  <div>
    <div class="d-flex align-center ga-3 mb-5">
      <VBtn
        icon="tabler-arrow-left"
        variant="text"
        to="/saas/tenants"
        aria-label="Back to complexes"
      />
      <div>
        <h1 class="text-h4 mb-1">
          Add complex
        </h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Create a separate tenant database, run its migrations, and configure an initial Owner.
        </p>
      </div>
    </div>

    <VAlert
      v-if="error"
      type="error"
      variant="tonal"
      class="mb-4"
    >
      {{ error }}
    </VAlert>

    <VAlert
      v-if="tenant"
      :type="tenant.owner_created && ownerEmailDeliveryStatus === 'sent' ? 'success' : 'warning'"
      variant="tonal"
      class="mb-4"
    >
      <div class="font-weight-medium">
        {{ tenant.owner_created
          ? 'Tenant database and migrations are ready. The Owner account has been created.'
          : tenant.tenant_database_ready
            ? 'Tenant database and migrations are ready, but the Owner account was not created.'
            : 'Complex created, but tenant provisioning is not complete.' }}
      </div>
      <div class="mt-1">
        <template v-if="ownerEmailDeliveryStatus === 'sent'">
          Temporary credentials were emailed. The Owner must change the password at first login.
        </template>
        <template v-else-if="tenant.owner_created">
          Email was not delivered ({{ ownerEmailDeliveryStatus }}). Use the one-time password below and share it securely.
        </template>
        <template v-else>
          {{ ownerEmailDeliveryStatus === 'not_sent'
            ? 'The Owner account was not created, so no credentials email was sent. Check readiness and retry Owner setup.'
            : 'Check onboarding readiness before allowing staff to use this complex.' }}
        </template>
      </div>
      <VList
        density="compact"
        class="bg-transparent mt-2"
      >
        <VListItem
          title="Tenant database"
          :subtitle="tenant.tenant_database_ready ? 'Created and reachable' : 'Not provisioned'"
        />
        <VListItem
          title="Tenant migrations"
          :subtitle="tenant.tenant_migrations_ready ? 'Core schema is ready' : 'Not complete'"
        />
      </VList>
      <div
        v-if="tenant.owner_created && temporaryPassword"
        class="mt-4"
      >
        <VTextField
          :model-value="temporaryPassword"
          label="One-time temporary Owner password"
          readonly
          density="compact"
          hide-details
          class="mb-2"
        />
        <VBtn
          variant="tonal"
          prepend-icon="tabler-copy"
          @click="copyTemporaryPassword"
        >
          Copy password
        </VBtn>
        <div
          v-if="passwordCopyError"
          class="text-caption text-error mt-2"
        >
          {{ passwordCopyError }}
        </div>
      </div>
      <VBtn
        class="mt-3"
        variant="tonal"
        :to="`/saas/tenants/${tenant.uuid}`"
      >
        Review onboarding
      </VBtn>
    </VAlert>

    <VForm
      v-if="!tenant"
      ref="formRef"
      @submit.prevent="save"
    >
      <VRow>
        <VCol
          cols="12"
          lg="8"
        >
          <VCard
            variant="outlined"
            class="mb-4"
          >
            <VCardItem>
              <template #prepend>
                <VAvatar
                  color="primary"
                  variant="tonal"
                  rounded="lg"
                >
                  <VIcon icon="tabler-building-community" />
                </VAvatar>
              </template>
              <VCardTitle>Complex details</VCardTitle>
              <VCardSubtitle>Name and primary host for the new complex.</VCardSubtitle>
            </VCardItem>
            <VDivider />
            <VCardText class="pt-6">
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.name"
                    label="Complex name"
                    :rules="[requiredValidator]"
                    autocomplete="organization"
                    required
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.slug"
                    label="Tenant slug"
                    :rules="[requiredValidator, slugRule]"
                    maxlength="57"
                    hint="Up to 57 characters. Used to generate the unique tenant database and host prefix."
                    persistent-hint
                    required
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="form.base_domain"
                    label="Domain suffix"
                    :rules="[domainRule]"
                    hint="For example, mtmp.local creates alpine.mtmp.local."
                    persistent-hint
                  />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>

          <VCard variant="outlined">
            <VCardItem>
              <template #prepend>
                <VAvatar
                  color="info"
                  variant="tonal"
                  rounded="lg"
                >
                  <VIcon icon="tabler-user" />
                </VAvatar>
              </template>
              <VCardTitle>Initial Owner account</VCardTitle>
              <VCardSubtitle>
                Optional. When provided, Owner credentials are emailed if a delivery mailer is configured.
              </VCardSubtitle>
            </VCardItem>
            <VDivider />
            <VCardText class="pt-6">
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.owner_email"
                    label="Owner email"
                    type="email"
                    autocomplete="email"
                    :rules="[ownerEmailRule]"
                  />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>

        <VCol
          cols="12"
          lg="4"
        >
          <VCard
            variant="outlined"
            class="saas-create-side"
          >
            <VCardText>
              <div class="d-flex align-center ga-3 mb-4">
                <VAvatar
                  color="warning"
                  variant="tonal"
                  rounded="lg"
                >
                  <VIcon icon="tabler-database" />
                </VAvatar>
                <div>
                  <div class="text-body-1 font-weight-medium">
                    Tenant provisioning
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    After registry creation
                  </div>
                </div>
              </div>
              <VAlert
                type="info"
                variant="tonal"
                density="comfortable"
                icon="tabler-info-circle"
              >
                This creates a separate PostgreSQL database for this tenant and runs all configured tenant migrations. Existing databases are never reset or reused.
              </VAlert>
            </VCardText>
            <VDivider />
            <VCardActions class="pa-4 flex-column align-stretch ga-2">
              <VBtn
                type="submit"
                size="large"
                :loading="saving"
                :disabled="saving"
              >
                Create complex
              </VBtn>
              <VBtn
                variant="outlined"
                to="/saas/tenants"
                :disabled="saving"
              >
                Cancel
              </VBtn>
            </VCardActions>
          </VCard>
        </VCol>
      </VRow>
    </VForm>
  </div>
</template>

<style scoped>
@media (min-width: 1280px) {
  .saas-create-side {
    position: sticky;
    top: 88px;
  }
}
</style>
