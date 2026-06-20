<script setup lang="ts">
import type { Achievement } from '@/types/game'

defineProps<{
    achievement: Achievement
    unlocked?: boolean
}>()
</script>

<template>
    <div
        :class="[
            'retro-card p-3 flex items-center gap-3 transition-all',
            unlocked ? 'pixel-border-cyan glow-cyan' : 'opacity-50 grayscale',
        ]"
    >
        <div
            :class="[
                'w-12 h-12 flex items-center justify-center text-2xl rounded-sm flex-shrink-0',
                unlocked ? 'bg-neon-purple/20 border border-neon-purple' : 'bg-retro-surface border border-retro-border',
            ]"
        >
            {{ achievement.icon }}
        </div>
        <div>
            <p class="retro-title text-xs text-white">{{ achievement.name }}</p>
            <p class="text-retro-muted text-xs mt-1">{{ achievement.description }}</p>
            <p v-if="achievement.reward_elo > 0" class="text-neon-green text-xs mt-1 retro-title">
                +{{ achievement.reward_elo }} ELO
            </p>
        </div>
        <div v-if="unlocked" class="ml-auto text-neon-green retro-title text-lg">✓</div>
    </div>
</template>
