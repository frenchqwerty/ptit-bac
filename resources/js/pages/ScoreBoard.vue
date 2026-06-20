<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AnimatedScore from '@/components/game/AnimatedScore.vue'
import { usePlayerStore } from '@/stores/playerStore'
import { useGameStore } from '@/stores/gameStore'

const props = defineProps<{ code: string }>()
const playerStore = usePlayerStore()
const gameStore = useGameStore()

onMounted(() => {
    if (!gameStore.gameFinishedData) {
        router.visit('/lobby')
    }
})

const sortedPlayers = computed(() => {
    return gameStore.gameFinishedData?.players ?? []
})

const winner = computed(() => gameStore.gameFinishedData?.winner)

function goToLobby(): void {
    gameStore.resetRound()
    router.visit('/lobby')
}
</script>

<template>
    <div class="min-h-screen p-4 max-w-2xl mx-auto space-y-6">
        <!-- Victory Header -->
        <div class="text-center pt-6 space-y-3">
            <div class="text-5xl">🏆</div>
            <h1 class="retro-title text-xl text-neon-yellow medal-gold">
                PARTIE TERMINÉE!
            </h1>
            <p v-if="winner" class="retro-title text-sm text-neon-cyan">
                {{ winner.name }} REMPORTE LA VICTOIRE!
            </p>
        </div>

        <!-- Final Scores -->
        <div class="space-y-3">
            <h2 class="retro-title text-xs text-retro-muted">CLASSEMENT FINAL</h2>

            <div
                v-for="(entry, idx) in sortedPlayers"
                :key="entry.id"
                :class="[
                    'retro-card p-4 flex items-center gap-4',
                    idx === 0 ? 'pixel-border-cyan glow-cyan' : 'pixel-border',
                ]"
            >
                <span
                    :class="[
                        'retro-title text-xl flex-shrink-0',
                        idx === 0 ? 'medal-gold' : idx === 1 ? 'medal-silver' : idx === 2 ? 'medal-bronze' : 'text-retro-muted',
                    ]"
                >
                    {{ idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : `#${idx + 1}` }}
                </span>

                <div class="flex-1">
                    <p class="retro-title text-sm text-white">{{ entry.name }}</p>
                </div>

                <div class="text-right">
                    <p class="retro-title text-xl text-neon-green">
                        <AnimatedScore :score="entry.score" :delay="idx * 200" />
                    </p>
                    <p class="text-retro-muted text-xs">pts</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button class="btn-retro btn-retro-cyan flex-1" @click="goToLobby">
                ← RETOUR AU LOBBY
            </button>
        </div>
    </div>
</template>
