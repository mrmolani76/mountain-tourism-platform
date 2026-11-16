<script setup lang="ts">
/**
 * Core tenant settings — currency, timezone, units, tax rates.
 * API: GET/PATCH /api/settings (owner).
 */

useHead({ title: 'Settings' })

definePageMeta({
  navActiveLink: 'settings',
})

const { $toast } = useNuxtApp()
const { loading, fetchGeneralSettings, updateGeneralSetting } = useGeneralSettingApiClient()
const refForm = ref()
const saving = ref(false)
const taxRatesText = ref('')

const form = ref<Partial<GeneralSetting>>({
  currency: 'EUR',
  timezone: 'Europe/Zurich',
  units: 'metric',
  tax_rates: [],
})

const parseTaxRates = (raw: string): number[] => {
  return raw
    .split(/[,\s]+/)
    .map(part => part.trim())
    .filter(Boolean)
    .map(part => Number(part))
    .filter(n => Number.isFinite(n) && n >= 0)
}

const load = async () => {
  const settings = await fetchGeneralSettings()
  if (!settings)
    return

  form.value = {
    id: settings.id,
    currency: settings.currency || 'EUR',
    timezone: settings.timezone || 'Europe/Zurich',
    units: settings.units === 'imperial' ? 'imperial' : 'metric',
    tax_rates: Array.isArray(settings.tax_rates) ? settings.tax_rates : [],
  }
  taxRatesText.value = (form.value.tax_rates || []).join(', ')
}

const save = async () => {
  const { valid } = await refForm.value.validate()
  if (!valid)
    return

  saving.value = true
  try {
    const response = await updateGeneralSetting({
      currency: String(form.value.currency || '').trim().toUpperCase(),
      timezone: String(form.value.timezone || '').trim(),
      units: form.value.units === 'imperial' ? 'imperial' : 'metric',
      tax_rates: parseTaxRates(taxRatesText.value),
    })

    if (response?.status) {
      $toast.success(response.message || 'Settings saved.')
      if (response.data) {
        form.value = { ...response.data }
        taxRatesText.value = (response.data.tax_rates || []).join(', ')
      }
    }
  }
  finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="mb-4">
      <h2 class="text-h5 mb-1">
        Settings
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Complex currency, timezone, units and tax rates.
      </p>
    </div>

    <template v-if="loading">
      <VSkeletonLoader type="article, actions" />
    </template>

    <VForm
      v-else
      ref="refForm"
      @submit.prevent="save"
    >
      <VCard flat>
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="form.currency"
                label="Currency"
                placeholder="EUR"
                :rules="[requiredValidator]"
                maxlength="8"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="form.timezone"
                label="Timezone"
                placeholder="Europe/Zurich"
                :rules="[requiredValidator]"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="form.units"
                label="Units"
                :items="[
                  { title: 'Metric', value: 'metric' },
                  { title: 'Imperial', value: 'imperial' },
                ]"
                :rules="[requiredValidator]"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="taxRatesText"
                label="Tax rates (%)"
                hint="Comma-separated, e.g. 7.7, 2.5"
                persistent-hint
                placeholder="7.7, 2.5"
              />
            </VCol>
          </VRow>
        </VCardText>
        <VCardActions class="px-4 pb-4">
          <VBtn
            color="primary"
            type="submit"
            :loading="saving"
            :disabled="saving"
          >
            Save
          </VBtn>
        </VCardActions>
      </VCard>
    </VForm>
  </div>
</template>
