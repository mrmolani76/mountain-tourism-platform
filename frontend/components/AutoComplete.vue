<script lang="ts" setup>
defineOptions({
    name: 'AutoComplete',
    inheritAttrs: false,
})

const test = ref()
const attrs = useAttrs()

// const { class: _class, label, variant: _, ...restAttrs } = useAttrs()

const elementId = computed(() => {
    const _elementIdToken = attrs?.id || attrs?.label

    return _elementIdToken ? `app-autocomplete-${_elementIdToken}-${Math.random().toString(36).slice(2, 7)}` : undefined
})

const selectedItemsTitleStr = computed(() => {
    const str = []

    const modelValueIsArrayOfObject = attrs.modelValue.length > 0 && typeof attrs.modelValue[0] === 'object'

    if (modelValueIsArrayOfObject) {
        for (const data of attrs.modelValue)
            str.push(data[attrs['item-title']])
    }
    else {
        for (const item of attrs.items) {
            if (attrs.modelValue.includes(item[attrs['item-value']]))
                str.push(item[attrs['item-title']])
        }
    }

    return str.join(', ')
})
</script>

<template>
    <div>
        <VTooltip v-if="$attrs.multiple && $attrs.maxShowSelected && $attrs.modelValue?.length > 0" activator="parent">
            {{ selectedItemsTitleStr }}
        </VTooltip>
        <VAutocomplete ref="test" v-bind="{
            ...$attrs,
            id: elementId,
        }">
            <template v-for="(_, name) in $slots" #[name]="slotProps">
                <slot :name="name" v-bind="slotProps || {}" />
            </template>
            <template #item="{ props, item }">
                <template v-if="item.raw.header">
                    <VListItem v-bind="props" :disabled="true"
                        class="tw-font-bold tw-opacity-100 tw-rounded-none tw-p-0 tw-m-0" variant="tonal" />
                </template>
                <VListItem v-else v-bind="props" :disabled="item.raw.disabled" />
            </template>
            <template v-if="$attrs.multiple && $attrs.maxShowSelected" #selection="{ item, index }">
                <div v-if="$attrs.maxShowSelected == 0">
                    <span v-if="index == 0" class="text-caption align-self-center">
                        {{ $attrs.modelValue?.length }} {{ $t('selected') }}
                    </span>
                </div>
                <div v-else>
                    <VChip v-if="index < $attrs.maxShowSelected" class="tw-max-w-32">
                        <span class="tw-truncate">{{ item.title }}</span>
                    </VChip>
                    <span v-if="index == $attrs.maxShowSelected" class="text-grey text-caption align-self-center">
                        (+{{ $attrs.modelValue?.length - $attrs.maxShowSelected }} {{ $t('others') }})
                    </span>
                </div>
            </template>
        </VAutocomplete>
    </div>
</template>
