<script setup lang="ts">
withDefaults(defineProps<{
    variant?: 'purple' | 'cyan' | 'pink' | 'red' | 'green'
    size?: 'sm' | 'md' | 'lg'
    loading?: boolean
    disabled?: boolean
}>(), {
    variant: 'purple',
    size: 'md',
    loading: false,
    disabled: false,
})

defineEmits<{ click: [] }>()
</script>

<template>
    <button
        :class="[
            'btn-retro',
            `btn-retro-${variant === 'purple' ? '' : variant}`,
            size === 'sm' ? 'text-xs py-2 px-3' : size === 'lg' ? 'text-base py-4 px-8' : '',
        ]"
        :disabled="disabled || loading"
        v-bind="$attrs"
    >
        <span v-if="loading" class="pixel-blink mr-2">▶</span>
        <slot />
    </button>
</template>
