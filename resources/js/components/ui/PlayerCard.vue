<script setup lang="ts">
import type { Player } from '@/types/game'

defineProps<{
    player: Player
    rank?: number
    showElo?: boolean
    isCurrentPlayer?: boolean
}>()
</script>

<template>
    <div
        :class="[
            'retro-card flex items-center gap-3 p-3 transition-all',
            isCurrentPlayer ? 'pixel-border-cyan glow-cyan' : 'pixel-border',
        ]"
    >
        <!-- Rank Badge -->
        <div v-if="rank" class="flex-shrink-0 w-8 text-center">
            <span
                :class="[
                    'retro-title text-sm',
                    rank === 1 ? 'medal-gold' : rank === 2 ? 'medal-silver' : rank === 3 ? 'medal-bronze' : 'text-retro-muted',
                ]"
            >
                {{ rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : `#${rank}` }}
            </span>
        </div>

        <!-- Avatar -->
        <div
            class="flex-shrink-0 w-10 h-10 rounded-sm flex items-center justify-center font-bold text-white retro-title text-xs"
            :style="{ backgroundColor: player.avatar_color }"
        >
            {{ player.display_name.charAt(0).toUpperCase() }}
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
            <p class="retro-title text-xs text-white truncate">{{ player.display_name }}</p>
            <p v-if="showElo" class="text-retro-muted text-xs mt-1">ELO: {{ player.elo_rating }}</p>
        </div>

        <!-- Online indicator -->
        <div class="flex-shrink-0">
            <span
                :class="[
                    'inline-block w-2 h-2 rounded-full',
                    player.is_online ? 'bg-neon-green shadow-[0_0_5px_rgba(16,185,129,0.8)]' : 'bg-retro-muted',
                ]"
            />
        </div>
    </div>
</template>
