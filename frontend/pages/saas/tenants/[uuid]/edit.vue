<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'
import type { SaasTenant } from '@/composables/api-clients/useSaasApiClient'

definePageMeta({
  layout: 'saas',
  title: 'Edit complex',
})

const route = useRoute()
const api = useSaasApiClient()
const formRef = ref<VForm>()
const loading = ref(true)
const loaded = ref(false)
const saving = ref(false)
const error = ref('')
const tenant = ref<SaasTenant | null>(null)
const form = ref({ name: '' })
const tenantUuid = String(route.params.uuid)

useHead({ title: 'Edit complex' })

const load = async () => {
  loading.value = true
  error.value = ''

  try {
    const tenantResponse = await api.getTenant(tenantUuid)
    if (!tenantResponse) {
      error.value = 'Complex details could not be loaded.'
      return
    }

    tenant.value = tenantResponse
    form.value.name = tenantResponse.name
    loaded.value = true
  }
  finally {
    loading.value = false
  }
}

const save = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid || saving.value)
    return

  saving.value = true
  error.value = ''

  try {
    const tenant = await api.updateTenant(tenantUuid, { name: form.value.name.trim() })
    if (!tenant) {
      error.value = 'Complex details could not be saved.'
      return
    }

    await navigateTo(`/saas/tenants/${tenantUuid}`)
  }
  finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex align-center ga-3 mb-5">
      <VBtn
        icon="tabler-arrow-left"
        variant="text"
        :to="`/saas/tenants/${tenantUuid}`"
        aria-label="Back to complex"
      />
      <h1 class="text-h4 mb-0">
        Edit complex
      </h1>
    </div>

    <VAlert
      v-if="error"
      type="error"
      variant="tonal"
      class="mb-4"
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

    <VCard
      v-if="loading"
      variant="outlined"
      max-width="640"
    >
      <VSkeletonLoader type="article, actions" />
    </VCard>

    <VCard
      v-else-if="loaded"
      variant="outlined"
      max-width="640"
    >
      <VCardText>
        <VForm
          ref="formRef"
          @submit.prevent="save"
        >
          <VRow>
            <VCol
              cols="12"
              lg="8"
            >
              <VCard variant="outlined">
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
                  <VCardSubtitle>Update the display name for this complex.</VCardSubtitle>
                </VCardItem>
                <VDivider />
                <VCardText class="pt-6">
                  <VTextField
                    v-model="form.name"
                    label="Complex name"
                    :rules="[requiredValidator]"
                    autocomplete="organization"
                    required
                  />
                </VCardText>
              </VCard>
            </VCol>

            <VCol
              cols="12"
              lg="4"
            >
              <VCard variant="outlined">
                <VCardTitle>
                  Registry identity
                </VCardTitle>
                <VCardText>
                  <VList density="compact">
                    <VListItem
                      title="Tenant slug"
                      :subtitle="tenant?.slug || '—'"
                    />
                    <VListItem
                      title="Primary domain"
                      :subtitle="tenant?.primary_domain || tenant?.domains[0] || 'Not set'"
                    />
                  </VList>
                  <p class="text-caption text-medium-emphasis mb-0">
                    Slug and domain stay locked after registration.
                  </p>
                </VCardText>
                <VDivider />
                <VCardActions class="pa-4 flex-column align-stretch ga-2">
                  <VBtn
                    type="submit"
                    :loading="saving"
                    :disabled="saving"
                  >
                    Save changes
                  </VBtn>
                  <VBtn
                    variant="outlined"
                    :to="`/saas/tenants/${tenantUuid}`"
                    :disabled="saving"
                  >
                    Cancel
                  </VBtn>
                </VCardActions>
              </VCard>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
