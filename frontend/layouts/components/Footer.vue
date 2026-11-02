<script setup>
import { pascalize } from '@iconify/utils';
import { themeConfig } from '@/themeConfig';

const title = pascalize(themeConfig.app.title)
</script>

<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <!-- 👉 Footer: left content -->
    <span class="d-flex align-center text-medium-emphasis">
      &copy;
      {{ new Date().getFullYear() }}

      {{ title }}
    </span>
    <!-- 👉 Footer: right content -->
    <span class="d-md-flex gap-x-4 text-primary d-none" />
  </div>
</template>
