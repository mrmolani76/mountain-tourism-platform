<script setup lang="ts">
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import miscMaskDark from '@images/pages/misc-mask-dark.png'
import miscMaskLight from '@images/pages/misc-mask-light.png'

const { t } = useI18n()
const authThemeMask = useGenerateImageVariant(miscMaskLight, miscMaskDark)

definePageMeta({
  navActiveLink: 'index',
})
</script>

<template>
  <div>
    <ErrorHeader
      :title="t('recordNotFound')"
      :description="t('recordNotFoundDescription')"
    />

    <div
      class="tw-mt-4 tw-fixed tw-bottom-0"
      style="inline-size:85rem"
    >
      <VImg
        :src="authThemeMask"
        class="misc-footer-img d-none d-md-block"
      />
    </div>
  </div>
</template>
