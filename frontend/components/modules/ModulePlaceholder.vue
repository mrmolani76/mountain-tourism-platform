<script setup lang="ts">
const props = defineProps<{
  moduleKey: string
}>()

const moduleData = computed(() => getAppModule(props.moduleKey))
</script>

<template>
  <template v-if="moduleData">
    <div class="d-flex flex-wrap align-center justify-space-between gap-3">
      <div class="d-flex align-center gap-3">
        <VBtn
          icon="tabler-arrow-left"
          variant="text"
          :to="{ path: '/' }"
        />
        <div>
          <h1 class="text-h4 mb-1">
            {{ moduleData.title }}
          </h1>
          <p class="text-medium-emphasis mb-0">
            {{ moduleData.description }}
          </p>
        </div>
      </div>

      <VChip
        color="primary"
        variant="tonal"
        prepend-icon="tabler-api"
      >
        {{ moduleData.endpoint }}
      </VChip>
    </div>

    <VRow class="mt-6">
      <VCol
        cols="12"
        md="7"
      >
        <VCard>
          <VCardItem>
            <template #prepend>
              <VAvatar
                color="primary"
                variant="tonal"
              >
                <VIcon :icon="moduleData.icon" />
              </VAvatar>
            </template>

            <VCardTitle>Module scope</VCardTitle>
            <VCardSubtitle>
              API is live. This screen is a placeholder until the module UI is built.
            </VCardSubtitle>
          </VCardItem>

          <VCardText>
            <VList lines="two">
              <VListItem
                v-for="feature in moduleData.features"
                :key="feature"
              >
                <template #prepend>
                  <VIcon
                    icon="tabler-circle-check"
                    color="success"
                    class="me-3"
                  />
                </template>
                <VListItemTitle>{{ feature }}</VListItemTitle>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="5"
      >
        <VCard>
          <VCardText>
            <div class="text-sm text-medium-emphasis">
              Backend endpoint
            </div>
            <div class="text-body-1 font-weight-medium mt-1">
              {{ moduleData.endpoint }}
            </div>

            <VDivider class="my-4" />

            <div class="text-sm text-medium-emphasis">
              API schema
            </div>
            <div class="text-body-1 font-weight-medium mt-1">
              /api/schema/
            </div>

            <VAlert
              v-if="moduleData.key === 'folio'"
              type="warning"
              variant="tonal"
              icon="tabler-lock"
              class="mt-4"
            >
              Money is append-only. Use voids with a reason, user and timestamp.
            </VAlert>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VAlert
      type="info"
      variant="tonal"
      icon="tabler-tool"
      class="mt-6"
    >
      Next step: build the {{ moduleData.title }} screens against this API.
      The route and navigation are ready.
    </VAlert>
  </template>

  <VAlert
    v-else
    type="error"
    variant="tonal"
  >
    Unknown module.
  </VAlert>
</template>
