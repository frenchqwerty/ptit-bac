<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import RoundTimer from '@/components/game/RoundTimer.vue'
import CategoryInput from '@/components/game/CategoryInput.vue'
import ScoreTable from '@/components/game/ScoreTable.vue'
import VotingPanel from '@/components/game/VotingPanel.vue'
import AchievementCard from '@/components/game/AchievementCard.vue'
import { usePlayerStore } from '@/stores/playerStore'
import { useGameStore } from '@/stores/gameStore'
import type { Category } from '@/types/game'

const props = defineProps<{ code: string }>()

const playerStore = usePlayerStore()
const gameStore = useGameStore()

let timerInterval: ReturnType<typeof setInterval> | null = null

const isHost = computed(() =>
    gameStore.room?.host?.id === playerStore.player?.id
)

const myPlayerId = computed(() => playerStore.player?.id ?? 0)

const categories: { key: Category; label: string; icon: string }[] = [
    { key: 'city', label: 'VILLE', icon: '🏙️' },
    { key: 'country', label: 'PAYS', icon: '🌍' },
    { key: 'first_name', label: 'PRÉNOM', icon: '👤' },
    { key: 'pokemon', label: 'POKÉMON', icon: '⚡' },
    { key: 'brand', label: 'MARQUE', icon: '🏷️' },
    { key: 'movie', label: 'FILM', icon: '🎬' },
]

onMounted(async () => {
    if (!playerStore.isAuthenticated && playerStore.token) {
        await playerStore.loadMe()
    }
    if (!playerStore.isAuthenticated) {
        router.visit('/')
        return
    }
    if (!gameStore.room) {
        await gameStore.loadRoom(props.code)
    }
    subscribeToChannel()
    startTimer()
})

onUnmounted(() => {
    if (timerInterval) { clearInterval(timerInterval) }
    echo().leave(`game.${props.code}`)
})

function startTimer(): void {
    if (timerInterval) { clearInterval(timerInterval) }
    timerInterval = setInterval(() => {
        gameStore.syncRemainingSeconds()
        if (gameStore.remainingSeconds <= 0 && gameStore.isRoundActive) {
            gameStore.isRoundActive = false
            submitAnswers().then(() => {
                // Only the host stops the round to avoid concurrent stop requests
                if (isHost.value) {
                    stopRound()
                }
            })
        }
    }, 1000)
}

function subscribeToChannel(): void {
    echo().join(`game.${props.code}`)
        .listen('.round.stopped', () => {
            gameStore.isRoundActive = false
        })
        .listen('.voting.started', (e: any) => {
            gameStore.onVotingStarted(e, myPlayerId.value)
        })
        .listen('.vote.submitted', (e: any) => {
            gameStore.onVoteSubmitted(e)
        })
        .listen('.scores.calculated', (e: any) => {
            gameStore.onScoresCalculated(e)
        })
        .listen('.round.finished', (e: { is_last_round: boolean }) => {
            if (e.is_last_round) {
                // Will be replaced by game.finished
            } else {
                // Next round will come via letter.generated
            }
        })
        .listen('.letter.generated', (e: any) => {
            gameStore.onLetterGenerated(e)
            startTimer()
        })
        .listen('.game.finished', (e: any) => {
            gameStore.onGameFinished(e)
            router.visit(`/scoreboard/${props.code}`)
        })
        .listen('.achievement.unlocked', (e: any) => {
            gameStore.onAchievementUnlocked(e)
        })
}

async function submitAnswers(): Promise<void> {
    if (gameStore.hasSubmitted) { return }
    await gameStore.submitAnswers()
}

async function stopRound(): Promise<void> {
    await submitAnswers()
    await gameStore.stopRound()
}

async function handleVoteSubmit(): Promise<void> {
    await gameStore.submitVotes(myPlayerId.value)
}

async function handleForceClose(): Promise<void> {
    await gameStore.forceCloseVoting()
}
</script>

<template>
    <div class="min-h-screen p-4 max-w-2xl mx-auto space-y-4">
        <!-- Game Header -->
        <div class="flex items-center justify-between pt-4">
            <div>
                <p class="retro-title text-xs text-retro-muted">
                    MANCHE {{ gameStore.currentRoundNumber }}/{{ gameStore.room?.total_rounds ?? 5 }}
                </p>
                <p class="retro-title text-xs text-neon-cyan mt-1">
                    {{ gameStore.room?.code }}
                </p>
            </div>

            <!-- Timer (hidden during voting/scoring) -->
            <div v-if="gameStore.room?.status === 'playing'" class="w-48">
                <RoundTimer
                    :seconds="gameStore.remainingSeconds"
                    :total-seconds="gameStore.room?.round_duration ?? 90"
                />
            </div>
        </div>

        <!-- Current Letter -->
        <div class="text-center py-4">
            <p class="retro-title text-xs text-retro-muted mb-2">LA LETTRE EST...</p>
            <div class="letter-display">{{ gameStore.currentLetter ?? '?' }}</div>
        </div>

        <!-- Voting Phase -->
        <div v-if="gameStore.isVotingPhase && gameStore.room?.status === 'voting'">
            <VotingPanel
                :players="gameStore.votingAnswers"
                :my-votes="gameStore.myVotes"
                :my-player-id="myPlayerId"
                :voting-progress="gameStore.votingProgress"
                :has-voted="gameStore.hasVoted"
                :is-host="isHost"
                :letter="gameStore.currentLetter ?? '?'"
                :round-number="gameStore.currentRoundNumber"
                @vote="(playerId, category, isValid) => gameStore.setVote(playerId, category, isValid)"
                @submit="handleVoteSubmit"
                @force-close="handleForceClose"
            />
        </div>

        <!-- Score Phase: show ScoreTable -->
        <div v-else-if="gameStore.room?.status === 'scoring' && gameStore.lastRoundScores">
            <ScoreTable
                :scores="gameStore.lastRoundScores"
                :players="gameStore.room?.players ?? []"
                :letter="gameStore.currentLetter ?? '?'"
                :round-number="gameStore.currentRoundNumber"
            />
        </div>

        <!-- Playing Phase: show CategoryInputs -->
        <div v-else class="space-y-3">
            <CategoryInput
                v-for="cat in categories"
                :key="cat.key"
                :category="cat.key"
                :label="cat.label"
                :icon="cat.icon"
                :letter="gameStore.currentLetter ?? '?'"
                :model-value="gameStore.answers[cat.key]"
                :disabled="gameStore.hasSubmitted || !gameStore.isRoundActive"
                @update:model-value="gameStore.setAnswer(cat.key, $event)"
            />

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-2">
                <button
                    :disabled="gameStore.hasSubmitted"
                    class="btn-retro btn-retro-cyan flex-1"
                    @click="submitAnswers"
                >
                    {{ gameStore.hasSubmitted ? '✓ SOUMIS' : '💾 VALIDER' }}
                </button>

                <button
                    class="btn-retro btn-retro-red flex-1"
                    @click="stopRound"
                >
                    🛑 STOP!
                </button>
            </div>

            <p v-if="gameStore.hasSubmitted" class="text-center retro-title text-xs text-neon-green pixel-blink">
                Réponses envoyées! En attente des autres joueurs...
            </p>
        </div>

        <!-- Achievement Toasts -->
        <div class="fixed bottom-4 right-4 space-y-2 z-50">
            <div
                v-for="achievement in gameStore.unlockedAchievements"
                :key="achievement.achievement.key"
                class="achievement-toast retro-card pixel-border-cyan glow-cyan p-3 flex items-center gap-3 max-w-xs"
            >
                <span class="text-2xl">🏆</span>
                <div>
                    <p class="retro-title text-xs text-neon-cyan">SUCCÈS DÉBLOQUÉ!</p>
                    <p class="retro-title text-xs text-white">{{ achievement.achievement.name }}</p>
                </div>
                <button
                    class="ml-auto text-retro-muted hover:text-white"
                    @click="gameStore.clearAchievement(achievement.achievement.key)"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>
</template>
