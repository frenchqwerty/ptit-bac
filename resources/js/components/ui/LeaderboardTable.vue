<script setup lang="ts">
import type { Player } from '@/types/game'

defineProps<{
    players: Player[]
    currentPlayerId?: number
}>()
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="(player, idx) in players"
            :key="player.id"
            :class="[
                'retro-card flex items-center gap-3 p-3 transition-all',
                player.id === currentPlayerId ? 'pixel-border-cyan' : 'pixel-border',
            ]"
        >
            <!-- Rank -->
            <div class="w-8 text-center flex-shrink-0">
                <span
                    :class="[
                        'retro-title text-sm',
                        idx === 0 ? 'medal-gold' : idx === 1 ? 'medal-silver' : idx === 2 ? 'medal-bronze' : 'text-retro-muted',
                    ]"
                >
                    {{ idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : `${idx + 1}` }}
                </span>
            </div>

            <!-- Avatar -->
            <div
                class="w-8 h-8 rounded-sm flex items-center justify-center text-white retro-title text-xs flex-shrink-0"
                :style="{ backgroundColor: player.avatar_color }"
            >
                {{ player.display_name.charAt(0).toUpperCase() }}
            </div>

            <!-- Name -->
            <div class="flex-1 min-w-0">
                <p class="retro-title text-xs text-white truncate">{{ player.display_name }}</p>
                <p class="text-retro-muted text-xs">{{ player.statistic?.games_played ?? 0 }} parties</p>
            </div>

            <!-- ELO -->
            <div class="text-right flex-shrink-0">
                <p class="retro-title text-sm text-neon-cyan">{{ player.elo_rating }}</p>
                <p class="text-retro-muted text-xs">ELO</p>
            </div>
        </div>

        <p v-if="players.length === 0" class="text-center text-retro-muted retro-title text-xs py-8">
            Aucun joueur encore...
        </p>
    </div>
</template>
