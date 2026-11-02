<script setup lang="ts">
const actionProgressDialog = ref(false)
const actionProgressPercentage = ref(0)
const actionProgressText = ref('')

let resolveCallback: (() => void) | null = null
let subscribedChannel: any = null
let subscribedEvent = '.bulkActionProgress'

const resetState = () => {
  actionProgressPercentage.value = 0
  actionProgressText.value = ''
}

const cleanup = () => {
  if (subscribedChannel && subscribedEvent) {
    try {
      subscribedChannel.stopListening(subscribedEvent)
    }
    catch (_) {
      // no-op
    }
  }
  subscribedChannel = null
}

watch(actionProgressDialog, value => {
  if (!value) {
    // Dialog closed by user -> cleanup and resolve pending promise
    cleanup()
    resolveCallback?.()
    resolveCallback = null
  }
})

onBeforeUnmount(() => cleanup())

onMounted(() => {
  const showBulkActionProgress = async (channel: string, event?: string) => {
    resetState()
    actionProgressDialog.value = true

    subscribedEvent = event || '.bulkActionProgress'

    // Subscribe to provided private channel and listen to progress events
    const connection: any = await socketStore().getConnection.then((res: any) => res.private(channel))

    subscribedChannel = connection

    connection.listen(subscribedEvent, (response: any) => {
      actionProgressPercentage.value = response.progressPercentage
      actionProgressText.value = response.text
    })

    return new Promise<void>(resolve => {
      resolveCallback = resolve
    })
  }

  // Expose global function similar to ConfirmDialog/Alert
  ;(window as any).showBulkActionProgress = showBulkActionProgress
})
</script>

<template>
  <ActionProgressDialog
    v-model="actionProgressDialog"
    :progressPercentage="actionProgressPercentage"
    :prependProgressText="actionProgressText"
  />
</template>
