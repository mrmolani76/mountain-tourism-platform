<script setup lang="ts">
definePageMeta({ navActiveLink: 'transport' })
useHead({ title: 'Lift lobby board' })

const client = useTransportApiClient()
const loading = ref(false)
const rows = ref<Array<Record<string, any>>>([])

async function load() {
  loading.value = true
  rows.value = (await client.lobbyLifts())?.results ?? []
  loading.value = false
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn variant="text" prepend-icon="tabler-arrow-left" :to="{ path: '/transport' }" class="mb-1 px-0">Transport</VBtn>
        <h2 class="text-h5 mb-1">Lobby lift status</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Public status API snapshot (lobby TV page is Phase 5).</p>
      </div>
      <VBtn variant="tonal" prepend-icon="tabler-refresh" :loading="loading" @click="load">Refresh</VBtn>
    </div>

    <VRow>
      <VCol v-for="row in rows" :key="row.id" cols="12" sm="6" md="4">
        <VCard>
          <VCardText>
            <div class="text-h6 mb-1">{{ row.name }}</div>
            <div class="text-caption text-medium-emphasis mb-2">{{ row.lift_type }}</div>
            <VChip size="small" variant="tonal" :color="row.status === 'open' ? 'success' : row.status === 'closed' ? 'error' : 'warning'">
              {{ row.status }}
            </VChip>
            <div v-if="row.reason" class="text-body-2 mt-2">{{ row.reason }}</div>
            <div v-if="row.updated_at" class="text-caption text-medium-emphasis mt-2">{{ row.updated_at }}</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol v-if="!loading && !rows.length" cols="12" class="text-center text-medium-emphasis py-8">
        No active lifts.
      </VCol>
    </VRow>
  </div>
</template>
