<script setup lang="ts">
const _ = defineProps({
  url: {
    type: String,
    required: true,
  },
  extension: {
    type: String,
    default: 'unknown',
  },
  loaded: {
    type: Boolean,
    default: false,
  },
});

const { getViewFileExtensionConfig } = useSharedApiClient();
const fileExtensionConfig = await getViewFileExtensionConfig();

const allowedImageFileExtention = fileExtensionConfig.allowedImageFile;
</script>

<template>
  <template v-if="url && loaded && allowedImageFileExtention.includes(extension)">
    <VImg
      class="mx-auto tw-h-full tw-w-full"
      :src="url"
      style="object-fit:contain;"
    >
      <template #placeholder>
        <div class="d-flex align-center justify-center fill-height">
          <VProgressCircular
            color="grey-lighten-4"
            indeterminate
          />
        </div>
      </template>
    </VImg>
  </template>

  <!-- url exist and file loaded but not allow to display that extension file -->
  <template v-else-if="url && loaded && !allowedImageFileExtention.includes(extension)">
    <VAlert
      density="compact"
      color="info"
      variant="tonal"
    >
      <span>{{ $t('imageCannotLoadedInViewer', { extension: extension || 'unknown' }) }}</span>
    </VAlert>
  </template>

  <!-- url exist but waiting to be loaded -->
  <div
    v-else-if="url && !loaded"
    class="tw-p-4"
  >
    <VAlert
      density="compact"
      color="info"
      variant="tonal"
    >
      {{ $t('loadingViewerDetails') }}
    </VAlert>
  </div>

  <!-- no url exist -->
  <div
    v-else-if="!url"
    class="tw-p-4"
  >
    <VAlert
      density="compact"
      color="info"
      variant="tonal"
    >
      {{ $t('imageNotUploaded') }}
    </VAlert>
  </div>
  <template v-else-if="loaded">
    <VAlert
      density="compact"
      color="warning"
      variant="tonal"
    >
      <span>
        {{ $t('imageFileUrlMissing') }} ({{ extension }})
      </span>
    </VAlert>
  </template>
</template>
