<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import RoomCard from '@/components/ui/RoomCard.vue'
import PixelModal from '@/components/ui/PixelModal.vue'
import { roomsApi } from '@/lib/api'
import { usePlayerStore } from '@/stores/playerStore'
import { useGameStore } from '@/stores/gameStore'
import type { GameRoom } from '@/types/game'

const playerStore = usePlayerStore()
const gameStore = useGameStore()
const rooms = ref<GameRoom[]>([])
const isLoading = ref(false)
const showCreateModal = ref(false)
const joinCode = ref('')
const createOptions = ref({ total_rounds: 5, round_duration: 90, max_players: 10 })

onMounted(async () => {
    // Restore session from token if player not yet loaded
    if (!playerStore.isAuthenticated && playerStore.token) {
        await playerStore.loadMe()
    }
    if (!playerStore.isAuthenticated) {
        router.visit('/')
        return
    }
    await loadRooms()
})

async function loadRooms(): Promise<void> {
    isLoading.value = true
    try {
        const res = await roomsApi.list()
        rooms.value = res.rooms
    } finally {
        isLoading.value = false
    }
}

async function createRoom(): Promise<void> {
    const room = await gameStore.createRoom(createOptions.value)
    showCreateModal.value = false
    router.visit(`/room/${room.code}`)
}

async function joinRoom(code: string): Promise<void> {
    try {
        const room = await gameStore.joinRoom(code || joinCode.value.toUpperCase())
        router.visit(`/room/${room.code}`)
    } catch (e) {
        alert(e instanceof Error ? e.message : 'Impossible de rejoindre la room')
    }
}
</script>

<template>
    <div class="min-h-screen p-4 max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pt-4">
            <div>
                <h1 class="retro-title text-lg text-neon-purple text-glow-purple">LOBBY</h1>
                <p class="text-retro-muted text-xs mt-1">
                    Bienvenue, <span class="text-white">{{ playerStore.player?.display_name }}</span>
                    · ELO <span class="text-neon-cyan">{{ playerStore.player?.elo_rating }}</span>
                </p>
            </div>
            <div class="flex gap-2">
                <button class="btn-retro btn-retro-cyan text-xs" @click="router.visit('/leaderboard')">
                    🏆 TOP
                </button>
                <button class="btn-retro btn-retro-pink text-xs" @click="router.visit('/profile')">
                    👤 PROFIL
                </button>
            </div>
        </div>

        <!-- Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <button class="btn-retro btn-retro-green py-6 text-base" @click="showCreateModal = true">
                ➕ CRÉER UNE ROOM
            </button>

            <div class="flex gap-2">
                <input
                    v-model="joinCode"
                    class="pixel-input flex-1"
                    placeholder="CODE DE LA ROOM"
                    maxlength="8"
                    style="font-size: 0.65rem"
                    @keyup.enter="joinRoom('')"
                />
                <button class="btn-retro btn-retro-cyan" @click="joinRoom('')">
                    ▶ JOIN
                </button>
            </div>
        </div>

        <!-- Rooms List -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="retro-title text-xs text-retro-muted">ROOMS DISPONIBLES</h2>
                <button class="text-neon-cyan text-xs retro-title" @click="loadRooms">↻ REFRESH</button>
            </div>

            <div v-if="isLoading" class="text-center py-12 retro-title text-xs text-retro-muted pixel-blink">
                CHARGEMENT...
            </div>

            <div v-else-if="rooms.length === 0" class="text-center py-12 retro-title text-xs text-retro-muted">
                AUCUNE ROOM OUVERTE<br>
                <span class="text-neon-cyan">SOIS LE PREMIER À EN CRÉER UNE!</span>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <RoomCard
                    v-for="room in rooms"
                    :key="room.code"
                    :room="room"
                    @join="joinRoom"
                />
            </div>
        </div>
    </div>

    <!-- Create Room Modal -->
    <PixelModal title="NOUVELLE ROOM" :show="showCreateModal" @close="showCreateModal = false">
        <div class="space-y-4">
            <div>
                <label class="retro-title text-xs text-retro-muted block mb-2">NOMBRE DE MANCHES</label>
                <input v-model.number="createOptions.total_rounds" type="number" min="1" max="10" class="pixel-input" />
            </div>
            <div>
                <label class="retro-title text-xs text-retro-muted block mb-2">DURÉE PAR MANCHE (SEC)</label>
                <input v-model.number="createOptions.round_duration" type="number" min="30" max="300" class="pixel-input" />
            </div>
            <div>
                <label class="retro-title text-xs text-retro-muted block mb-2">MAX JOUEURS</label>
                <input v-model.number="createOptions.max_players" type="number" min="2" max="10" class="pixel-input" />
            </div>
            <button class="btn-retro w-full" @click="createRoom">▶ CRÉER</button>
        </div>
    </PixelModal>
</template>
