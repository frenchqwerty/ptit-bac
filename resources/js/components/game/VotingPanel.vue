<script setup lang="ts">
import type { Category, VotingPlayerAnswer } from '@/types/game'
import { computed } from 'vue'

const props = defineProps<{
    players: VotingPlayerAnswer[]
    myVotes: Record<string, Record<string, boolean>>
    myPlayerId: number
    votingProgress: { voted: number; total: number }
    hasVoted: boolean
    isHost: boolean
    letter: string
    roundNumber: number
}>()

const emit = defineEmits<{
    (e: 'vote', playerId: number, category: string, isValid: boolean): void
    (e: 'submit'): void
    (e: 'forceClose'): void
}>()

const categoryIcons: Record<string, string> = {
    city: '🏙️',
    country: '🌍',
    first_name: '👤',
    pokemon: '⚡',
    brand: '🏷️',
    movie: '🎬',
}

const categories = computed<Category[]>(() => ['city', 'country', 'first_name', 'pokemon', 'brand', 'movie'])

const otherPlayers = computed(() => props.players.filter(p => p.player_id !== props.myPlayerId))

const myAnswers = computed(() => props.players.find(p => p.player_id === props.myPlayerId))

const isReadyToSubmit = computed(() => {
    if (otherPlayers.value.length === 0) { return true }
    // All other players' answers have a vote
    return otherPlayers.value.every(pa => {
        const playerVotes = props.myVotes[pa.player_id] ?? {}
        return categories.value.every(cat => cat in playerVotes)
    })
})

function getVote(playerId: number, category: string): boolean | undefined {
    return props.myVotes[playerId]?.[category]
}

const remainingVoteCount = computed(() => {
    if (otherPlayers.value.length === 0) { return 0 }
    return otherPlayers.value.reduce((total, pa) => {
        const voted = props.myVotes[pa.player_id] ?? {}
        return total + categories.value.filter(cat => !(cat in voted)).length
    }, 0)
})
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="text-center space-y-1">
            <p class="retro-title text-xs text-retro-muted">VOTE — MANCHE {{ roundNumber }} — LETTRE</p>
            <p class="letter-display">{{ letter }}</p>
        </div>

        <!-- Progress -->
        <div class="retro-card p-3 flex items-center justify-between">
            <span class="retro-title text-xs text-retro-muted">VOTES SOUMIS</span>
            <span class="retro-title text-sm text-neon-cyan">
                {{ votingProgress.voted }}/{{ votingProgress.total }}
            </span>
        </div>

        <!-- My own answers (read-only) -->
        <div v-if="myAnswers" class="retro-card p-3 space-y-2 opacity-70">
            <div class="flex items-center gap-2 mb-2">
                <div
                    class="w-6 h-6 rounded-sm flex items-center justify-center text-xs text-white retro-title"
                    :style="{ backgroundColor: myAnswers.avatar_color }"
                >
                    {{ myAnswers.display_name.charAt(0) }}
                </div>
                <span class="retro-title text-xs text-retro-muted">{{ myAnswers.display_name }} (toi)</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1">
                <div
                    v-for="cat in categories"
                    :key="cat"
                    class="text-xs p-1 rounded-sm border border-retro-muted/20 bg-retro-muted/5"
                >
                    <span class="mr-1">{{ categoryIcons[cat] }}</span>
                    <span class="text-retro-muted">{{ myAnswers.answers[cat]?.value || '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Other players to vote on -->
        <div
            v-for="pa in otherPlayers"
            :key="pa.player_id"
            class="retro-card p-3 space-y-3"
        >
            <!-- Player header -->
            <div class="flex items-center gap-2">
                <div
                    class="w-6 h-6 rounded-sm flex items-center justify-center text-xs text-white retro-title"
                    :style="{ backgroundColor: pa.avatar_color }"
                >
                    {{ pa.display_name.charAt(0) }}
                </div>
                <span class="retro-title text-xs text-white">{{ pa.display_name }}</span>
            </div>

            <!-- Per-category voting -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div
                    v-for="cat in categories"
                    :key="cat"
                    class="flex items-center justify-between gap-2 p-2 rounded-sm border border-retro-muted/20"
                >
                    <!-- Category + answer -->
                    <div class="flex items-center gap-1 min-w-0 flex-1">
                        <span class="text-sm shrink-0">{{ categoryIcons[cat] }}</span>
                        <span
                            :class="[
                                'text-xs truncate',
                                pa.answers[cat]?.value ? 'text-white' : 'text-retro-muted italic',
                            ]"
                        >
                            {{ pa.answers[cat]?.value || '—' }}
                        </span>
                    </div>

                    <!-- Vote buttons -->
                    <div v-if="!hasVoted" class="flex gap-1 shrink-0">
                        <button
                            :class="[
                                'w-7 h-7 rounded-sm retro-title text-sm transition-all',
                                getVote(pa.player_id, cat) === true
                                    ? 'bg-neon-green/30 border border-neon-green text-neon-green'
                                    : 'border border-retro-muted/30 text-retro-muted hover:border-neon-green hover:text-neon-green',
                            ]"
                            @click="emit('vote', pa.player_id, cat, true)"
                        >
                            ✓
                        </button>
                        <button
                            :class="[
                                'w-7 h-7 rounded-sm retro-title text-sm transition-all',
                                getVote(pa.player_id, cat) === false
                                    ? 'bg-neon-red/30 border border-neon-red text-neon-red'
                                    : 'border border-retro-muted/30 text-retro-muted hover:border-neon-red hover:text-neon-red',
                            ]"
                            @click="emit('vote', pa.player_id, cat, false)"
                        >
                            ✗
                        </button>
                    </div>

                    <!-- Already voted — show final vote -->
                    <div v-else class="shrink-0">
                        <span
                            v-if="getVote(pa.player_id, cat) === true"
                            class="retro-title text-sm text-neon-green"
                        >✓</span>
                        <span
                            v-else
                            class="retro-title text-sm text-neon-red"
                        >✗</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit / force-close buttons -->
        <div class="space-y-3 pt-1">
            <button
                v-if="!hasVoted"
                :disabled="!isReadyToSubmit"
                :class="[
                    'w-full btn-retro py-4 text-base transition-all',
                    isReadyToSubmit
                        ? 'btn-retro-green glow-green'
                        : 'opacity-30 cursor-not-allowed',
                ]"
                @click="emit('submit')"
            >
                <template v-if="isReadyToSubmit">✓ SOUMETTRE MES VOTES</template>
                <template v-else>{{ remainingVoteCount }} VOTE{{ remainingVoteCount > 1 ? 'S' : '' }} RESTANT{{ remainingVoteCount > 1 ? 'S' : '' }}</template>
            </button>

            <div v-else class="retro-card p-3 text-center">
                <p class="retro-title text-xs text-neon-green">✓ VOTES SOUMIS</p>
                <p class="retro-title text-xs text-retro-muted mt-1">EN ATTENTE DES AUTRES…</p>
            </div>

            <button
                v-if="isHost"
                class="w-full btn-retro py-2 text-xs opacity-50 hover:opacity-100"
                @click="emit('forceClose')"
            >
                ⚡ HÔTE — FORCER LA CLÔTURE
            </button>
        </div>
    </div>
</template>
