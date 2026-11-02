<script setup lang="ts">
const props = defineProps({
  max: {
    type: Number,
    default: 0,
  },
  current: {
    type: Number,
    default: 0,
  },
  variant: {
    type: String,
    default: 'plain',
  },
  totalUsers: {
    type: Number,
    default: 0,
  },
  signedUsers: {
    type: Number,
    default: 0,
  },
  color: {
    type: String,
    default: '#000000',
  },
  label: {
    type: String,
    required: true,
  },
  prependIcon: {
    type: String,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  skippedStatuses: {
    type: Array,
    default: [],
  },
})

const emit = defineEmits(['click'])
const randomId = ref(Math.random().toString(36).substring(2, 10))

const fillValue = (dotNumber: number) => {
  if (dotNumber < props.current && !props.skippedStatuses.includes(dotNumber))
    return props.color
  else if (dotNumber == props.current && props.signedUsers)
    return `url(#myGradient${randomId.value})`
  else
    return 'transparent'
}
</script>

<template>
  <div class="tw-w-fit">
    <div
      v-if="variant == 'plain'"
      class="tw-font-semibold"
    >
      {{ label }}
    </div>
    <div v-else-if="variant == 'button'">
      <VBtn
        :color="color"
        block
        class="tw-mb-0"
        :prependIcon="prependIcon"
        :disabled="disabled"
        @click="emit('click')"
      >
        {{ label }}
      </VBtn>
    </div>
    <div
      v-if="max"
      class="tw-flex tw-justify-start tw-items-start"
    >
      <div v-for="dot in max">
        <svg
          width="1em"
          viewBox="0 0 10 10"
          xmlns="http://www.w3.org/2000/svg"
        >
          <defs>
            <linearGradient :id="`myGradient${randomId}`">
              <stop
                :offset="`${Math.round(signedUsers / totalUsers * 100)}%`"
                :stop-color="color"
              />
              <stop
                offset="0%"
                stop-color="transparent"
              />
            </linearGradient>
          </defs>

          <!-- Circle (background) -->
          <circle
            cx="5"
            cy="5"
            r="4"
            :fill="fillValue(dot)"
            :stroke="color"
            stroke-width="1"
          />

          <!-- If skipped, draw X over the dot -->
          <template v-if="skippedStatuses.includes(dot)">
            <line
              x1="2"
              y1="2"
              x2="8"
              y2="8"
              :stroke="color"
              stroke-width="1"
            />
            <line
              x1="8"
              y1="2"
              x2="2"
              y2="8"
              :stroke="color"
              stroke-width="1"
            />
          </template>
        </svg>
      </div>
    </div>
  </div>
</template>
