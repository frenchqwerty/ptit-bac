<script setup lang="ts">
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AchievementCard from '@/components/game/AchievementCard.vue'
import { useAchievementStore } from '@/stores/achievementStore'
import { usePlayerStore } from '@/stores/playerStore'

const playerStore = usePlayerStore()
const achievementStore = useAchievementStore()

onMounted(async () => {
    if (!playerStore.isAuthenticated && playerStore.token) {
        await playerStore.loadMe()
    }
    if (!playerStore.isAuthenticated) {
        router.visit('/')
        return
    }
    await achievementStore.loadProfile()
})
</script>

<template>
    <div class="min-h-screen p-4 max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pt-4">
            <h1 class="retro-title text-lg text-neon-purple">👤 PROFIL</h1>
            <button class="btn-retro btn-retro-cyan text-xs" @click="router.visit('/lobby')">
                ← LOBBY
            </button>
        </div>

        <!-- Player Card -->
        <div class="retro-card pixel-border-cyan p-5 space-y-3">
            <div class="flex items-center gap-4">
                <div
                    class="w-16 h-16 rounded-sm flex items-center justify-center text-white retro-title text-2xl"
                    :style="{ backgroundColor: playerStore.player?.avatar_color }"
                >
                    {{ playerStore.player?.display_name.charAt(0).toUpperCase() }}
                </div>
                <div>
                    <p class="retro-title text-lg text-white">{{ playerStore.player?.display_name }}</p>
                    <p class="retro-title text-sm text-neon-cyan mt-1">ELO: {{ playerStore.player?.elo_rating }}</p>
                    <p class="text-retro-muted text-xs mt-1">Best: {{ playerStore.player?.best_elo }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div v-if="achievementStore.statistics">
            <h2 class="retro-title text-xs text-retro-muted mb-3">STATISTIQUES</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div class="retro-card p-3 text-center">
                    <p class="retro-title text-xl text-neon-purple">{{ achievementStore.statistics.games_played }}</p>
                    <p class="retro-title text-xs text-retro-muted mt-1">PARTIES</p>
                </div>
                <div class="retro-card p-3 text-center">
                    <p class="retro-title text-xl text-neon-yellow">{{ achievementStore.statistics.games_won }}</p>
                    <p class="retro-title text-xs text-retro-muted mt-1">VICTOIRES</p>
                </div>
                <div class="retro-card p-3 text-center">
                    <p class="retro-title text-xl text-neon-cyan">{{ achievementStore.statistics.best_score }}</p>
                    <p class="retro-title text-xs text-retro-muted mt-1">BEST SCORE</p>
                </div>
                <div class="retro-card p-3 text-center">
                    <p class="retro-title text-xl text-neon-green">{{ achievementStore.statistics.unique_answers_found }}</p>
                    <p class="retro-title text-xs text-retro-muted mt-1">UNIQUES</p>
                </div>
                <div class="retro-card p-3 text-center">
                    <p class="retro-title text-xl text-neon-pink">{{ achievementStore.statistics.best_win_streak }}</p>
                    <p class="retro-title text-xs text-retro-muted mt-1">STREAK</p>
                </div>
                <div class="retro-card p-3 text-center">
                    <p class="retro-title text-xl text-neon-yellow">{{ achievementStore.statistics.perfect_rounds }}</p>
                    <p class="retro-title text-xs text-retro-muted mt-1">PERFECTS</p>
                </div>
            </div>
        </div>

        <!-- Achievements -->
        <div>
            <h2 class="retro-title text-xs text-retro-muted mb-3">SUCCÈS</h2>
            <div class="space-y-2">
                <AchievementCard
                    v-for="ach in achievementStore.achievements"
                    :key="ach.key"
                    :achievement="ach"
                    unlocked
                />
                <p v-if="achievementStore.achievements.length === 0" class="text-retro-muted retro-title text-xs text-center py-6">
                    Aucun succès débloqué encore...
                </p>
            </div>
        </div>

        <!-- Logout -->
        <button class="btn-retro btn-retro-red w-full" @click="playerStore.logout(); router.visit('/')">
            ← SE DÉCONNECTER
        </button>
    </div>
</template>
