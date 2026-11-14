<script setup lang="ts">
definePageMeta({ navActiveLink: 'services' })
useHead({ title: 'Guided outings' })

const route = useRoute()
const client = useServicesApiClient()
const saving = ref(false)
const slotId = ref<number | null>(Number(route.query.slot_id) || null)
const outing = ref<Record<string, any> | null>(null)
const participants = ref<Array<Record<string, any>>>([])

async function openOuting() {
  if (!slotId.value)
    return
  saving.value = true
  const payload = await client.openOuting(slotId.value)
  saving.value = false
  if (!payload)
    return
  outing.value = payload
  participants.value = payload.participants ?? []
  showAlertDialog('Outing opened', `Outing #${payload.id} ready.`, 'OK')
}

async function markOut(row: Record<string, any>) {
  saving.value = true
  const updated = await client.markOut(row.id)
  saving.value = false
  if (updated)
    replaceParticipant(updated)
}

async function markBack(row: Record<string, any>) {
  saving.value = true
  const updated = await client.markBack(row.id)
  saving.value = false
  if (updated)
    replaceParticipant(updated)
}

async function closeOuting() {
  if (!outing.value?.id)
    return
  saving.value = true
  const updated = await client.closeOuting(outing.value.id)
  saving.value = false
  if (updated) {
    outing.value = updated
    showAlertDialog('Outing closed', 'All participants must be back before close.', 'OK')
  }
}

function replaceParticipant(updated: Record<string, any>) {
  const idx = participants.value.findIndex(p => p.id === updated.id)
  if (idx >= 0)
    participants.value[idx] = updated
  else
    participants.value.push(updated)
}
</script>

<template>
  <div>
    <div class="mb-4">
      <VBtn
        variant="text"
        prepend-icon="tabler-arrow-left"
        :to="{ path: '/services' }"
        class="mb-1 px-0"
      >
        Services
      </VBtn>
      <h2 class="text-h5 mb-1">
        Guided outings
      </h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Open from a slot, mark guests out/back, then close.
      </p>
    </div>

    <VCard class="mb-4">
      <VCardText class="d-flex flex-wrap gap-3 align-end">
        <AppTextField
          v-model.number="slotId"
          type="number"
          label="Slot ID"
          density="compact"
          style="min-inline-size: 160px"
        />
        <VBtn
          color="primary"
          :loading="saving"
          :disabled="!slotId"
          @click="openOuting"
        >
          Open outing
        </VBtn>
        <VBtn
          v-if="outing?.id"
          color="error"
          variant="tonal"
          :loading="saving"
          @click="closeOuting"
        >
          Close outing #{{ outing.id }}
        </VBtn>
      </VCardText>
    </VCard>

    <VAlert
      v-if="outing"
      type="info"
      variant="tonal"
      class="mb-4"
      density="compact"
    >
      Outing #{{ outing.id }} · status {{ outing.status || 'open' }} · slot {{ outing.slot_id || slotId }}
    </VAlert>

    <VCard>
      <VCardTitle class="text-body-1">
        Participants
      </VCardTitle>
      <VTable density="compact">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Status</th>
            <th class="text-end">
              Actions
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in participants"
            :key="row.id"
          >
            <td>{{ row.id }}</td>
            <td>{{ row.name || row.guest_name || '—' }}</td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="row.status === 'back' ? 'success' : row.status === 'out' ? 'warning' : 'default'"
              >
                {{ row.status }}
              </VChip>
            </td>
            <td class="text-end">
              <VBtn
                v-if="row.status !== 'out' && row.status !== 'back'"
                size="small"
                variant="tonal"
                class="me-1"
                :loading="saving"
                @click="markOut(row)"
              >
                Out
              </VBtn>
              <VBtn
                v-if="row.status === 'out'"
                size="small"
                color="primary"
                variant="tonal"
                :loading="saving"
                @click="markBack(row)"
              >
                Back
              </VBtn>
            </td>
          </tr>
          <tr v-if="!participants.length">
            <td
              colspan="4"
              class="text-center text-medium-emphasis py-8"
            >
              Open an outing from a booked slot to load participants.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
