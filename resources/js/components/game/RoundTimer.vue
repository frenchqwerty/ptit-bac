<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    seconds: number
    totalSeconds: number
}>()

const percentage = computed(() => {
    if (props.totalSeconds === 0) { return 0 }
    return Math.max(0, Math.min(100, (props.seconds / props.totalSeconds) * 100))
})

const isUrgent = computed(() => props.seconds <= 15)
const isWarning = computed(() => props.seconds <= 30 && !isUrgent.value)

const displayTime = computed(() => {
    const m = Math.floor(props.seconds / 60)
    const s = props.seconds % 60
    return `${m}:${String(s).padStart(2, '0')}`
})

const barColor = computed(() => {
    if (isUrgent.value) { return 'bg-neon-red' }
    if (isWarning.value) { return 'bg-neon-yellow' }
    return 'bg-neon-green'
})
</script>

<template>
    <div class="space-y-2">
        <!-- Time Display -->
        <div class="flex items-center justify-between">
            <span class="retro-title text-xs text-retro-muted">TEMPS</span>
            <span
                :class="[
                    'retro-title text-2xl',
                    isUrgent ? 'timer-urgent' : isWarning ? 'text-neon-yellow' : 'text-neon-green',
                ]"
            >
                {{ displayTime }}
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="pixel-progress">
            <div
                :class="['pixel-progress-fill', barColor]"
                :style="{ width: `${percentage}%` }"
            />
        </div>
    </div>
</template>
