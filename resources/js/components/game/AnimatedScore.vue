<script setup lang="ts">
import { onMounted, ref } from 'vue'

const props = defineProps<{
    score: number
    delay?: number
}>()

const displayScore = ref(0)
const isAnimating = ref(false)

onMounted(() => {
    setTimeout(() => {
        isAnimating.value = true
        animateTo(props.score)
    }, props.delay ?? 0)
})

function animateTo(target: number): void {
    const duration = 800
    const start = displayScore.value
    const startTime = performance.now()

    function update(now: number): void {
        const elapsed = now - startTime
        const progress = Math.min(elapsed / duration, 1)
        const eased = 1 - Math.pow(1 - progress, 3) // cubic ease-out
        displayScore.value = Math.round(start + (target - start) * eased)

        if (progress < 1) {
            requestAnimationFrame(update)
        }
    }

    requestAnimationFrame(update)
}
</script>

<template>
    <span
        :class="[
            'retro-title tabular-nums',
            isAnimating ? 'score-pop' : '',
        ]"
    >
        {{ displayScore }}
    </span>
</template>
