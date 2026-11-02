<script setup lang="ts">
const { getUser } = authStore();
const { getUserProfileSetting } = useUserApiClient();
const responseData = await getUserProfileSetting();
</script>

<template>
  <div>
    <p class="text-body-1">{{ $t('generalSettings') }}</p>
    <p class="text-caption text-disabled">{{ $t('noChangesWereMade') }}</p>
  </div>
</template>
