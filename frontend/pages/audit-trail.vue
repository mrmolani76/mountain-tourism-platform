<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'audit-trail',
})

useHead({
  title: t('auditTrail'),
})
</script>

<template>
  <div>
    <AuditTrail :module="MODULE_CORE" />
  </div>
</template>
