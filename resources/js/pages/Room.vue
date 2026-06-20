<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import PlayerCard from '@/components/ui/PlayerCard.vue'
import { usePlayerStore } from '@/stores/playerStore'
import { useGameStore } from '@/stores/gameStore'
import type { WsPlayerJoinedPayload } from '@/types/game'

const props = defineProps<{ code: string }>()

const playerStore = usePlayerStore()
const gameStore = useGameStore()

const isStarting = ref(false)
const errorMsg = ref('')
let pollInterval: ReturnType<typeof setInterval> | null = null

const isHost = computed(() =>
    gameStore.room?.host?.id === playerStore.player?.id
)

const canStart = computed(() =>
    isHost.value && (gameStore.room?.players_count ?? 0) >= 2
)

onMounted(async () => {
    if (!playerStore.isAuthenticated && playerStore.token) {
        await playerStore.loadMe()
    }
    if (!playerStore.isAuthenticated) {
        router.visit('/')
        return
    }
    await gameStore.loadRoom(props.code)
    console.log('[Room] player.id:', playerStore.player?.id, '| host.id:', gameStore.room?.host?.id, '| players_count:', gameStore.room?.players_count, '| isHost:', isHost.value)
    subscribeToChannel()
    // Fallback polling — updates players_count if WebSocket events are missed
    pollInterval = setInterval(() => gameStore.loadRoom(props.code), 5000)
})

onUnmounted(() => {
    echo().leave(`game.${props.code}`)
    if (pollInterval) { clearInterval(pollInterval) }
})

function subscribeToChannel(): void {
    echo().join(`game.${props.code}`)
        .here((users: unknown[]) => {
            console.log('Users in room:', users)
        })
        .joining((user: unknown) => {
            console.log('User joining:', user)
        })
        .leaving((user: unknown) => {
            console.log('User leaving:', user)
        })
        .listen('.player.joined', (e: WsPlayerJoinedPayload) => {
            gameStore.loadRoom(props.code)
        })
        .listen('.player.left', () => {
            gameStore.loadRoom(props.code)
        })
        .listen('.game.started', () => {
            // Room status will update via letter.generated
        })
        .listen('.letter.generated', (e: any) => {
            gameStore.onLetterGenerated(e)
            router.visit(`/game/${props.code}`)
        })
}

async function startGame(): Promise<void> {
    if (!canStart.value) { return }
    isStarting.value = true
    try {
        await gameStore.startGame()
    } catch (e) {
        errorMsg.value = e instanceof Error ? e.message : 'Erreur au démarrage'
    } finally {
        isStarting.value = false
    }
}

async function leaveRoom(): Promise<void> {
    await gameStore.leaveRoom()
    router.visit('/lobby')
}

function copyCode(): void {
    navigator.clipboard.writeText(props.code)
}
</script>

<template>
    <div class="min-h-screen p-4 max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pt-4">
            <div>
                <h1 class="retro-title text-lg text-neon-purple">SALLE D'ATTENTE</h1>
                <p class="text-retro-muted text-xs mt-1">En attente des joueurs...</p>
            </div>
            <button class="btn-retro btn-retro-red text-xs" @click="leaveRoom">✕ QUITTER</button>
        </div>

        <!-- Room Code -->
        <div class="retro-card pixel-border-cyan p-4 text-center">
            <p class="retro-title text-xs text-retro-muted mb-2">CODE DE LA ROOM</p>
            <p class="retro-title text-3xl text-neon-cyan text-glow-cyan tracking-widest">
                {{ gameStore.room?.code ?? code }}
            </p>
            <button class="text-retro-muted text-xs mt-2 retro-title hover:text-white" @click="copyCode">
                📋 COPIER
            </button>
        </div>

        <!-- Room info -->
        <div class="grid grid-cols-3 gap-2 text-center">
            <div class="retro-card p-3">
                <p class="retro-title text-lg text-neon-yellow">{{ gameStore.room?.total_rounds ?? 5 }}</p>
                <p class="retro-title text-xs text-retro-muted mt-1">MANCHES</p>
            </div>
            <div class="retro-card p-3">
                <p class="retro-title text-lg text-neon-cyan">{{ gameStore.room?.round_duration ?? 90 }}s</p>
                <p class="retro-title text-xs text-retro-muted mt-1">PAR MANCHE</p>
            </div>
            <div class="retro-card p-3">
                <p class="retro-title text-lg text-neon-green">{{ gameStore.room?.players_count ?? 0 }}/{{ gameStore.room?.max_players ?? 10 }}</p>
                <p class="retro-title text-xs text-retro-muted mt-1">JOUEURS</p>
            </div>
        </div>

        <!-- Players List -->
        <div>
            <h2 class="retro-title text-xs text-retro-muted mb-3">JOUEURS</h2>
            <div class="space-y-2">
                <PlayerCard
                    v-for="player in gameStore.room?.players ?? []"
                    :key="player.id"
                    :player="player"
                    :is-current-player="player.id === playerStore.player?.id"
                />
            </div>
        </div>

        <p v-if="errorMsg" class="retro-title text-xs text-neon-red pixel-blink text-center">
            ⚠ {{ errorMsg }}
        </p>

        <!-- Start Button (host only) -->
        <button
            v-if="isHost"
            :disabled="!canStart || isStarting"
            class="btn-retro btn-retro-green w-full py-4 text-base"
            @click="startGame"
        >
            <span v-if="isStarting" class="pixel-blink">DÉMARRAGE...</span>
            <span v-else-if="!canStart">EN ATTENTE DE JOUEURS...</span>
            <span v-else>▶▶ LANCER LA PARTIE</span>
        </button>

        <p v-else class="text-center retro-title text-xs text-retro-muted pixel-blink">
            En attente que l'hôte lance la partie...
        </p>
    </div>
</template>
