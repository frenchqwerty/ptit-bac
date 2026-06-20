<script setup lang="ts">
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import LeaderboardTable from '@/components/ui/LeaderboardTable.vue'
import { useLeaderboardStore } from '@/stores/leaderboardStore'
import { usePlayerStore } from '@/stores/playerStore'

const leaderboardStore = useLeaderboardStore()
const playerStore = usePlayerStore()

onMounted(async () => {
    await leaderboardStore.load()

    echo().channel('leaderboard')
        .listen('.leaderboard.updated', () => {
            leaderboardStore.refresh()
        })
})
</script>

<template>
    <div class="min-h-screen p-4 max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between pt-4">
            <h1 class="retro-title text-lg text-neon-yellow text-glow-purple">
                🏆 CLASSEMENT
            </h1>
            <button class="btn-retro btn-retro-cyan text-xs" @click="router.visit('/lobby')">
                ← LOBBY
            </button>
        </div>

        <div v-if="leaderboardStore.isLoading" class="text-center py-12 retro-title text-xs text-retro-muted pixel-blink">
            CHARGEMENT DU CLASSEMENT...
        </div>

        <div v-else>
            <LeaderboardTable
                :players="leaderboardStore.players"
                :current-player-id="playerStore.player?.id"
            />
        </div>
    </div>
</template>
