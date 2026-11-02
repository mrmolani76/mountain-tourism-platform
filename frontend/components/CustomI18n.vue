<script setup lang="ts">
import type { I18nLanguage } from '@layouts/types';

interface Props {
  languages: I18nLanguage[]
  location?: any
}

const props = withDefaults(defineProps<Props>(), {
  location: 'bottom end',
})

const { locale } = useI18n({ useScope: 'global' })
const { t } = useI18n();
const { updateUserLang } = useUserApiClient();

const updateLang = async (lang: string) => {
  if (await showConfirmDialog()) {
    await updateUserLang(lang);
    locale.value = lang
    reloadNuxtApp();
  }
}
</script>

<template>
  <IconBtn>
    <VIcon
      size="26"
      icon="tabler-language"
    />

    <!-- Menu -->
    <VMenu
      activator="parent"
      :location="props.location"
      offset="12px"
      width="175"
    >
      <!-- List -->
      <VList
        :selected="[locale]"
        color="primary"
      >
        <!-- List item -->
        <VListItem
          v-for="lang in props.languages"
          :key="lang.i18nLang"
          :value="lang.i18nLang"
          @click="updateLang(lang.i18nLang)"
        >
          <!-- Language label -->
          <VListItemTitle>
            {{ t(lang.label) }}
          </VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
  </IconBtn>
</template>
