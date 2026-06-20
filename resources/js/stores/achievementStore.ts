import { ref } from 'vue'
import { defineStore } from 'pinia'
import { profileApi } from '@/lib/api'
import type { Achievement, EloHistoryEntry, GameHistory, PlayerStatistic } from '@/types/game'

export const useAchievementStore = defineStore('achievement', () => {
    const achievements = ref<Achievement[]>([])
    const statistics = ref<PlayerStatistic | null>(null)
    const eloHistory = ref<EloHistoryEntry[]>([])
    const gameHistory = ref<GameHistory[]>([])
    const isLoading = ref(false)

    async function loadProfile(): Promise<void> {
        isLoading.value = true
        try {
            const result = await profileApi.show()
            achievements.value = result.achievements
            statistics.value = result.statistics
            eloHistory.value = result.elo_history
        } finally {
            isLoading.value = false
        }
    }

    async function loadHistory(page = 1): Promise<void> {
        const result = await profileApi.history(page)
        if (page === 1) {
            gameHistory.value = result.data
        } else {
            gameHistory.value.push(...result.data)
        }
    }

    return {
        achievements,
        statistics,
        eloHistory,
        gameHistory,
        isLoading,
        loadProfile,
        loadHistory,
    }
})
