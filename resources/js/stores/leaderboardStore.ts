import { ref } from 'vue'
import { defineStore } from 'pinia'
import { leaderboardApi } from '@/lib/api'
import type { Player } from '@/types/game'

export const useLeaderboardStore = defineStore('leaderboard', () => {
    const players = ref<Player[]>([])
    const isLoading = ref(false)
    const lastUpdatedAt = ref<Date | null>(null)

    async function load(): Promise<void> {
        isLoading.value = true
        try {
            const result = await leaderboardApi.index()
            players.value = result.leaderboard
            lastUpdatedAt.value = new Date()
        } finally {
            isLoading.value = false
        }
    }

    function refresh(): Promise<void> {
        return load()
    }

    return { players, isLoading, lastUpdatedAt, load, refresh }
})
