import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { authApi } from '@/lib/api'
import type { GameRoom, Player } from '@/types/game'

export const usePlayerStore = defineStore('player', () => {
    const player = ref<Player | null>(null)
    const token = ref<string | null>(localStorage.getItem('player_token'))
    const currentRoom = ref<GameRoom | null>(null)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    const isAuthenticated = computed(() => !!player.value && !!token.value)

    async function login(name: string): Promise<void> {
        isLoading.value = true
        error.value = null
        try {
            const result = await authApi.login(name)
            player.value = result.player
            token.value = result.token
            localStorage.setItem('player_token', result.token)
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Connexion échouée'
            throw e
        } finally {
            isLoading.value = false
        }
    }

    async function loadMe(): Promise<GameRoom | null> {
        if (!token.value) { return null }
        try {
            const result = await authApi.me()
            player.value = result.player
            currentRoom.value = result.current_room
            return result.current_room
        } catch {
            logout()
            return null
        }
    }

    function logout(): void {
        if (token.value) {
            authApi.logout().catch(() => {})
        }
        player.value = null
        token.value = null
        currentRoom.value = null
        localStorage.removeItem('player_token')
    }

    function setPlayer(p: Player): void {
        player.value = p
    }

    return {
        player,
        token,
        currentRoom,
        isLoading,
        error,
        isAuthenticated,
        login,
        loadMe,
        logout,
        setPlayer,
    }
})
