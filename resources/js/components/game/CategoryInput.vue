<script setup lang="ts">
import type { Category } from '@/types/game'

const props = defineProps<{
    category: Category
    label: string
    icon: string
    modelValue: string
    letter: string
    disabled?: boolean
    result?: { valid: boolean; unique: boolean; points: number }
}>()

const emit = defineEmits<{
    'update:modelValue': [string]
}>()

const startsWithLetter = computed(() => {
    const v = props.modelValue.trim()
    if (!v) { return null }
    return v.charAt(0).toUpperCase() === props.letter.toUpperCase()
})

import { computed } from 'vue'
</script>

<template>
    <div class="space-y-1">
        <label class="flex items-center gap-2 retro-title text-xs text-retro-muted">
            <span>{{ icon }}</span>
            <span>{{ label }}</span>
            <!-- Points badge when result is available -->
            <span
                v-if="result"
                :class="[
                    'ml-auto retro-title text-xs px-2 py-0.5',
                    result.points > 0 ? 'text-neon-green' : 'text-neon-red',
                ]"
            >
                +{{ result.points }}
                <span v-if="result.unique" class="text-neon-cyan">★</span>
            </span>
        </label>

        <div class="relative">
            <input
                :value="modelValue"
                :disabled="disabled"
                :class="[
                    'pixel-input',
                    disabled ? 'opacity-60 cursor-not-allowed' : '',
                    result?.valid === true ? 'border-neon-green' : '',
                    result?.valid === false && modelValue ? 'border-neon-red' : '',
                    startsWithLetter === false ? 'border-neon-yellow' : '',
                ]"
                :placeholder="`${label} en ${letter}...`"
                type="text"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="words"
                spellcheck="false"
                @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            />

            <!-- Letter indicator -->
            <div
                v-if="!disabled && modelValue && startsWithLetter !== null"
                :class="[
                    'absolute right-3 top-1/2 -translate-y-1/2 retro-title text-xs',
                    startsWithLetter ? 'text-neon-green' : 'text-neon-red',
                ]"
            >
                {{ startsWithLetter ? '✓' : '✗' }}
            </div>
        </div>
    </div>
</template>
