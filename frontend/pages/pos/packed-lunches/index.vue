<script setup lang="ts">
definePageMeta({ navActiveLink: 'pos' })
useHead({ title: 'Packed lunches' })

const client = usePosApiClient()
const loading = ref(false)
const forDate = ref(new Date().toISOString().slice(0, 10))
const rows = ref<Array<Record<string, any>>>([])

async function load() {
  loading.value = true
  const res = await client.kitchenPackedLunches({ for_date: forDate.value })
  rows.value = res?.results ?? []
  loading.value = false
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
      <div>
        <VBtn
          variant="text"
          prepend-icon="tabler-arrow-left"
          :to="{ path: '/pos' }"
          class="mb-1 px-0"
        >
          POS
        </VBtn>
        <h2 class="text-h5 mb-1">
          Packed lunches
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Kitchen list for the selected date.
        </p>
      </div>
      <VBtn
        variant="tonal"
        prepend-icon="tabler-refresh"
        :loading="loading"
        @click="load"
      >
        Refresh
      </VBtn>
    </div>

    <VCard class="mb-4">
      <VCardText>
        <AppTextField
          v-model="forDate"
          type="date"
          label="Date"
          density="compact"
          style="max-inline-size: 220px"
          @update:model-value="load"
        />
      </VCardText>
    </VCard>

    <VCard>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Guest</th>
            <th>Stay</th>
            <th>Status</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="4"
              class="text-center py-8"
            >
              <VProgressCircular
                indeterminate
                color="primary"
              />
            </td>
          </tr>
          <tr
            v-for="row in rows"
            v-else
            :key="row.id"
          >
            <td>{{ row.guest_name || '—' }}</td>
            <td>{{ row.stay_id || '—' }}</td>
            <td>{{ row.status }}</td>
            <td>{{ row.notes || '—' }}</td>
          </tr>
          <tr v-if="!loading && !rows.length">
            <td
              colspan="4"
              class="text-center text-medium-emphasis py-8"
            >
              No packed lunches for this date.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
