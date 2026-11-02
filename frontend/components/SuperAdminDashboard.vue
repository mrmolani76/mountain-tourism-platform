<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useDashboardApiClient } from '~/composables/api-clients/useDashboardApiClient';

const { getSuperUserDashboardCount } = useDashboardApiClient();

definePageMeta({
  layoutWrapperClasses: 'layout-content-height-fixed',
})

const countData = ref<DashboardCategory[]>([]);

onMounted(async () => {
  const result = await getSuperUserDashboardCount();

  if (!result)
    return;

  countData.value = [
    {
      icon: 'tabler-users',
      color: 'primary',
      title: 'totalUsers',
      value: result.totalUsers || 0,
      isHover: false,
    },
    {
      icon: 'tabler-user-check',
      color: 'success',
      title: 'activeUsers',
      value: result.activeUsers || 0,
      isHover: false,
    },
    {
      icon: 'tabler-user-x',
      color: 'error',
      title: 'inactiveUsers',
      value: result.inactiveUsers || 0,
      isHover: false,
    },
  ]
})
</script>

<template>
  <VRow class="match-height">
    <VCol cols="12">
      <VRow>
        <VCol
          v-for="(data, index) in countData"
          :key="index"
          cols="12"
          sm="6"
          md="4"
        >
          <VCard
            class="logistics-card-statistics position-relative"
            :style="data.isHover
              ? `border-block-end-color: rgb(var(--v-theme-${data.color}))`
              : `border-block-end-color: rgba(var(--v-theme-${data.color}),0.38)`"
            @mouseenter="data.isHover = true"
            @mouseleave="data.isHover = false"
          >
            <div class="tw-m-2">
              <div class="d-flex align-center gap-x-4 mb-1">
                <VAvatar
                  variant="tonal"
                  :color="data.color"
                  rounded
                >
                  <VIcon
                    :icon="data.icon"
                    size="28"
                  />
                </VAvatar>
                <h4 class="text-h4">
                  {{ data.value }}
                </h4>
              </div>

              <div class="tw-text-sm mb-1">
                {{ $t(data.title) }}
              </div>
            </div>
          </VCard>
        </VCol>
      </VRow>
    </VCol>
  </VRow>
</template>

<style lang="scss">
.logistics-card-statistics {
  border-block-end: 4px solid;
  transition: border-block-end-color 0.3s ease;
}
</style>
