<script lang="ts" setup>
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

const menuItems = appNavigation
</script>

<template>
  <VerticalNavLayout :navItems="menuItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="tw-flex h-100 align-center tw-justify-between">
        <div class="tw-flex tw-w-full">
          <IconBtn
            id="vertical-nav-toggle-btn"
            class="ms-n3 d-lg-none"
            @click="toggleVerticalOverlayNavActive(true)"
          >
            <VIcon
              size="26"
              icon="tabler-menu-2"
            />
          </IconBtn>
        </div>

        <div class="tw-flex tw-justify-end tw-items-center tw-shrink-0">
          <NavbarThemeSwitcher class="tw-me-2" />

          <CustomI18n
            v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
            class="tw-me-2"
            :languages="themeConfig.app.i18n.langConfig"
          />
          <UserProfile />
        </div>
      </div>
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>
