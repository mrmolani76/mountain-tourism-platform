<script setup lang="ts">
const props = defineProps({
  activityId: {
    type: Number,
    required: true,
  },
  isDialogOpen: {
    type: Boolean,
    required: true,
  },
  compareURL: {
    type: String,
    required: true,
  },
  entityId: {
    type: String,
    required: false,
  },
})

const emit = defineEmits(['update:isDialogOpen'])
const { t } = useI18n();
const compareData = ref<any[]>([]);
const pageLoading = ref(true)
const title = ref('');
const { getAuditTrailCompare } = useAuditTrailApiClient()

const updatedDialogValueUpdate = (val: boolean) => {
  emit('update:isDialogOpen', val)
}

const fetchAuditTrailCompare = async (params: object) => {
  pageLoading.value = true;

  const response = await getAuditTrailCompare(props.compareURL, params);

  compareData.value = response.compareData
  title.value = response.titleActivity
  pageLoading.value = false;
}

defineExpose({
  fetchAuditTrailCompare,
})
</script>

<template>
  <VDialog
    maxWidth="50%"
    :modelValue="isDialogOpen"
    @update:model-value="updatedDialogValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="$emit('update:isDialogOpen', false);" />
    <template v-if="pageLoading">
      <VSkeletonLoader
        :loading="pageLoading"
        type="table@1"
      />
    </template>
    <template v-else>
      <VCard :title="title">
        <VCardText>
          <VCol cols="12">
            <VTable>
              <thead>
                <tr>
                  <th scope="col">
                    {{ t('fieldName') }}
                  </th>
                  <th scope="col">
                    {{ t('oldValue') }}
                  </th>
                  <th scope="col">
                    {{ t('newValue') }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="compare in compareData"
                  :key="compare.fieldName"
                >
                  <td>
                    <span :class="compare.color == 'red' ? 'tw-text-red-700' : compare.color == 'green' ? 'tw-text-green-700' : 'tw-text-blue-700'">{{
                      t(compare.fieldName) }}</span>
                  </td>
                  <td>
                    <template v-if="Array.isArray(compare.oldValue)">
                      {{ compare.oldValue.join(', ') }}
                    </template>
                    <template v-if="compare.uniqueKey === 'servicesDetails'">
                      <VCard
                        variant="outlined"
                        class="tw-rounded-md"
                      >
                        <VCardText class="tw-p-3">
                          <div
                            v-for="(details, serviceName) in compare.oldValue"
                            :key="serviceName"
                            class="tw-mb-4 tw-mt-2"
                          >
                            <!-- Service Name -->
                            <div class="tw-font-semibold ">
                              {{ serviceName }}
                            </div>

                            <!-- Details Grid -->
                            <div class="tw-grid tw-grid-cols-2 tw-gap-x-6 tw-gap-y-1 tw-mt-2">
                              <div
                                v-for="(value, key) in details"
                                :key="key"
                                class="tw-flex tw-items-center"
                              >
                                <span class=" tw-text-sm">{{ $t(key) }} -</span>
                                <span class=" tw-ml-1 tw-text-sm">{{ value }}</span>
                              </div>
                            </div>
                          </div>
                        </VCardText>
                      </VCard>
                    </template>

                    <template v-else-if="compare.rte">
                      <div
                        class="resetListStyle"
                        v-html="compare.oldValue"
                      />
                    </template>
                    <template v-else>
                      <div>{{ compare.oldValue }}</div>
                    </template>
                  </td>
                  <td>
                    <template v-if="Array.isArray(compare.newValue)">
                      {{ compare.newValue.join(', ') }}
                    </template>
                    <template v-else-if="compare.rte">
                      <div
                        class="resetListStyle"
                        v-html="compare.newValue"
                      />
                    </template>
                    <template v-if="compare.uniqueKey === 'servicesDetails'">
                      <VCard
                        variant="outlined"
                        class="tw-rounded-md tw-mt-2"
                      >
                        <VCardText class="tw-p-3">
                          <div
                            v-for="(details, serviceName) in compare.newValue"
                            :key="serviceName"
                            class="tw-mb-4"
                          >
                            <!-- Service Name -->
                            <div class="tw-font-semibold ">
                              {{ serviceName }}
                            </div>

                            <!-- Details Grid -->
                            <div class="tw-grid tw-grid-cols-2 tw-gap-x-6 tw-gap-y-1 tw-mt-2">
                              <div
                                v-for="(value, key) in details"
                                :key="key"
                                class="tw-flex tw-items-center"
                              >
                                <span class=" tw-text-sm">{{ t(key) }} -</span>
                                <span class=" tw-ml-1 tw-text-sm">{{ value }}</span>
                              </div>
                            </div>
                          </div>
                        </VCardText>
                      </VCard>
                    </template>
                    <template v-else>
                      <div>{{ compare.newValue }}</div>
                    </template>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCol>
        </VCardText>
      </VCard>
    </template>
  </VDialog>
</template>
