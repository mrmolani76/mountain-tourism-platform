<script setup lang="ts">
const props = defineProps({
  progress: {
    type: Number,
    required: true,
  },
})

const { t } = useI18n();

const progressData = computed(() => {
  const progress = props.progress;
  let text;
  let color;

  switch (true) {
    case (progress <= 1):
    case (progress <= 2):
      text = t('weak');
      color = 'error';
      break;
    case (progress <= 3):
    case (progress <= 4):
      text = t('moderate');
      color = 'warning';
      break;
    case (progress <= 5):
    case (progress <= 6):
      text = t('veryStrong');
      color = 'success';
      break;
    default:
      text = t('unknown'); // Handle unexpected values if needed
      color = 'default'; // Provide a default color
      break;
  }

  if (progress > 2 && progress <= 3)
    text = t('moderate');
  else if (progress > 4 && progress <= 5)
    text = t('strong');
  else if (progress > 5 && progress <= 6)
    text = t('excellent');

  return { color, text };
});
</script>

<template>
  <div v-if="progress">
    <div class="tw-grid tw-grid-cols-4 tw-mt-2">
      <div class="tw-col-span-1">
        <VLabel>{{ progressData.text }}</VLabel>
      </div>
      <div class="tw-col-span-3 tw-mt-2">
        <VProgressLinear
          :modelValue="progress"
          :color="progressData.color"
          :height="8"
          :min="0"
          :max="6"
        />
      </div>
    </div>
  </div>
</template>
