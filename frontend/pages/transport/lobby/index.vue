<script setup lang="ts">
definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Lift lobby status' })

const client = useTransportApiClient()
const loading = ref(false)
const rows = ref<Array<Record<string, any>>>([])
let timer: ReturnType<typeof setInterval> | null = null

async function load() {
  loading.value = true
  rows.value = (await client.lobbyLifts())?.results ?? []
  loading.value = false
}

onMounted(() => {
  load()
  timer = setInterval(load, 30000)
})

onBeforeUnmount(() => {
  if (timer)
    clearInterval(timer)
})
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
        <h2 class="text-h5 mb-1">Lobby lift board</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Polls every 30s. Closed lifts show for lobby consumers.</p>
      </div>
      <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">Refresh</VBtn>
    </div>

    <VRow>
      <VCol v-for="row in rows" :key="row.id || row.lift_id" cols="12" sm="6" md="4">
        <VCard>
          <VCardText>
            <div class="text-h6">{{ row.name || row.lift_name }}</div>
            <VChip
              class="mt-3"
              size="large"
              variant="tonal"
              :color="(row.status || row.current_status) === 'open' ? 'success' : (row.status || row.current_status) === 'closed' ? 'error' : 'warning'"
            >
              {{ row.status || row.current_status || '—' }}
            </VChip>
            <div v-if="row.reason || row.current_status_reason" class="text-caption mt-2 text-medium-emphasis">
              {{ row.reason || row.current_status_reason }}
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol v-if="!loading && !rows.length" cols="12">
        <VAlert type="info" variant="tonal">No lift status yet.</VAlert>
      </VCol>
    </VRow>
  </div>
</template>
