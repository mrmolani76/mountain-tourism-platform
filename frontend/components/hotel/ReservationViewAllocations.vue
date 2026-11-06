<script setup lang="ts">
defineProps<{
  entityData?: Reservation | null
}>()
</script>

<template>
  <VCard>
    <div class="tw-mb-4 tw-text-lg tw-mt-4 tw-ml-4">
      Allocations
    </div>
    <VDivider
      :thickness="1"
      class="border-opacity-100"
      color="secondary"
    />
    <VTable density="comfortable">
      <thead>
        <tr>
          <th>Type</th>
          <th>Room</th>
          <th>Bed</th>
          <th class="text-end">
            Nightly
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(row, index) in entityData?.allocations ?? []"
          :key="row.id ?? index"
        >
          <td>{{ row.accommodation_type }}</td>
          <td>{{ row.room_id ?? '--' }}</td>
          <td>{{ row.hut_bed_id ?? '--' }}</td>
          <td class="text-end">
            {{ formatCents(row.nightly_price_cents) }}
          </td>
        </tr>
        <tr v-if="!(entityData?.allocations?.length)">
          <td
            colspan="4"
            class="text-medium-emphasis text-center"
          >
            No allocations.
          </td>
        </tr>
      </tbody>
    </VTable>
  </VCard>
</template>
