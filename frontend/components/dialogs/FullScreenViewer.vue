<script setup lang="ts">
const props = defineProps({
  viewerData: {
    type: Object,
    required: true,
  },
  dialogOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['dialogStateUpdated']);
const isDialogVisible = ref(props.dialogOpen);

watch(() => props.dialogOpen, () => {
  isDialogVisible.value = props.dialogOpen;
})
</script>

<template>
  <div>
    <VDialog
      v-model="isDialogVisible"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
      :update:modelValue="emit('dialogStateUpdated', isDialogVisible)"
    >
      <VCard>
        <!-- Toolbar -->
        <div>
          <VToolbar
            color="secondary"
            height="45"
          >
            <VToolbarTitle>
              <span>{{ viewerData.title }}</span>
            </VToolbarTitle>

            <VBtn
              icon
              variant="plain"
              @click="isDialogVisible = false"
            >
              <VIcon
                color="white"
                icon="tabler-x"
              />
            </VBtn>
          </VToolbar>
        </div>
        <FileViewer
          :url="viewerData.url"
          :extension="viewerData.extension"
          :loaded="dialogOpen"
        />
      </VCard>
    </VDialog>
  </div>
</template>
