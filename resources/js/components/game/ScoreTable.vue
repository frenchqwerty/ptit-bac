<script setup lang="ts">
import type { Player, ScoreResult } from '@/types/game'
import { computed } from 'vue'

const props = defineProps<{
    scores: Record<string, ScoreResult>
    players: Player[]
    letter: string
    roundNumber: number
}>()

const sortedPlayers = computed(() => {
    return props.players
        .map(p => ({
            player: p,
            score: props.scores[String(p.id)]?.round_score ?? 0,
            result: props.scores[String(p.id)],
        }))
        .sort((a, b) => b.score - a.score)
})

const categoryIcons: Record<string, string> = {
    city: '🏙️',
    country: '🌍',
    first_name: '👤',
    pokemon: '⚡',
    brand: '🏷️',
    movie: '🎬',
}
</script>

<template>
    <div class="space-y-4">
        <div class="text-center">
            <p class="retro-title text-xs text-retro-muted">MANCHE {{ roundNumber }} — LETTRE</p>
            <p class="letter-display">{{ letter }}</p>
        </div>

        <div class="space-y-3">
            <div
                v-for="(entry, idx) in sortedPlayers"
                :key="entry.player.id"
                class="retro-card p-3 space-y-2"
            >
                <!-- Player header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="retro-title text-sm">
                            {{ idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : `#${idx + 1}` }}
                        </span>
                        <div
                            class="w-6 h-6 rounded-sm flex items-center justify-center text-xs text-white retro-title"
                            :style="{ backgroundColor: entry.player.avatar_color }"
                        >
                            {{ entry.player.display_name.charAt(0) }}
                        </div>
                        <span class="retro-title text-xs text-white">{{ entry.player.display_name }}</span>
                    </div>
                    <span
                        :class="[
                            'retro-title text-sm',
                            entry.result?.has_perfect ? 'text-neon-cyan glow-cyan' : 'text-neon-green',
                        ]"
                    >
                        +{{ entry.score }}
                        <span v-if="entry.result?.has_perfect" class="text-xs ml-1">PERFECT!</span>
                    </span>
                </div>

                <!-- Answers breakdown -->
                <div v-if="entry.result" class="grid grid-cols-2 sm:grid-cols-3 gap-1">
                    <div
                        v-for="(detail, cat) in entry.result.categories"
                        :key="cat"
                        :class="[
                            'text-xs p-1 rounded-sm border',
                            detail.valid ? 'border-neon-green/40 bg-neon-green/10' : 'border-neon-red/40 bg-neon-red/10',
                        ]"
                    >
                        <span class="mr-1">{{ categoryIcons[cat] }}</span>
                        <span :class="detail.valid ? 'text-neon-green' : 'text-neon-red'">
                            {{ detail.value || '—' }}
                        </span>
                        <span v-if="detail.unique" class="text-neon-cyan ml-1">★</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
