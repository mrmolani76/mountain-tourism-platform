<script setup lang="ts">
const nuxtApp: any = useNuxtApp();
const { t } = useI18n();
const { loading, fetchGeneralSettings, updateGeneralSetting } = useGeneralSettingApiClient();
const refGeneralSettingForm = ref();
const isButtonDisabled = ref(false);

const generalSettingsForm = ref<Partial<GeneralSetting>>({
  // Base framework - generic settings can be added here as needed
});

const saveGeneralSetting = async () => {
  isButtonDisabled.value = true

  const response = await updateGeneralSetting(generalSettingsForm.value)

  if (response?.status) {
    nuxtApp.$toast.success(response.message, {
      onClose: () => {
        isButtonDisabled.value = false
      },
    });

    return;
  }

  isButtonDisabled.value = false
}

(async () => {
  useHead({
    title: t('generalSettings'),
  })

  const generalSettings = await fetchGeneralSettings();
  if (generalSettings)
    generalSettingsForm.value = generalSettings;
})();
</script>

<template>
  <div>
    <template v-if="loading">
      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-items-start">
        <div class="tw-col-span-2 tw-mb-5">
          <VSkeletonLoader
            :loading="loading"
            type="text"
          />
        </div>
        <div>
          <VSkeletonLoader
            :loading="loading"
            type="heading@10"
          />
        </div>
        <div>
          <VSkeletonLoader
            :loading="loading"
            type="heading@4"
          />
        </div>
      </div>
    </template>
    <template v-else>
      <VForm
        ref="refGeneralSettingForm"
        @submit.prevent="refGeneralSettingForm.validate().then(function (response:any) {
          response.valid ? saveGeneralSetting()
          : ''
        })"
      >
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-items-start">
          <VCard
            flat
            width="100%"
            class="tw-p-4"
          >
            <div class="tw-mb-2 tw-text-lg tw-mt-2 tw-ml-4">
              {{ t('generalSetting') }}
            </div>
            <VDivider
              :thickness="1"
              class="border-opacity-100"
              color="secondary"
            />
            <div class="tw-mt-4 tw-mb-4">
              <p class="text-body-1">{{ t('generalSettings') }}</p>
              <p class="text-caption text-disabled">{{ t('noChangesWereMade') }}</p>
            </div>
          </VCard>
        </div>
        <div class="w-100 sticky-footer mt-5 ">
          <VCard class="tw-w-full">
            <VCardText class="tw-flex tw-gap-2 tw-justify-start tw-p-2">
              <VBtn
                :disabled="isButtonDisabled"
                :loading="isButtonDisabled"
                color="primary"
                type="submit"
                name="save"
              >
                {{ t('save') }}
              </VBtn>
            </VCardText>
          </VCard>
        </div>
      </VForm>
    </template>
  </div>
</template>
