import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { roomsApi, roundsApi, votingApi } from '@/lib/api'
import type {
    Category,
    GameRoom,
    ScoreResult,
    VotingPlayerAnswer,
    WsAchievementUnlockedPayload,
    WsGameFinishedPayload,
    WsLetterGeneratedPayload,
    WsScoresCalculatedPayload,
    WsVoteSubmittedPayload,
    WsVotingStartedPayload,
} from '@/types/game'

export const useGameStore = defineStore('game', () => {
    const room = ref<GameRoom | null>(null)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // Round state
    const currentLetter = ref<string | null>(null)
    const currentRoundNumber = ref(0)
    const remainingSeconds = ref(0)
    const roundEndsAt = ref<string | null>(null)
    const isRoundActive = ref(false)
    const hasSubmitted = ref(false)

    // Answers input (player's current answers)
    const answers = ref<Record<Category, string>>({
        city: '',
        country: '',
        first_name: '',
        pokemon: '',
        brand: '',
        movie: '',
    })

    // Score results from last round
    const lastRoundScores = ref<Record<string, ScoreResult> | null>(null)
    const gameFinishedData = ref<WsGameFinishedPayload | null>(null)
    const unlockedAchievements = ref<WsAchievementUnlockedPayload[]>([])

    // Voting state
    const isVotingPhase = ref(false)
    const votingAnswers = ref<VotingPlayerAnswer[]>([])
    const myVotes = ref<Record<string, Record<string, boolean>>>({})
    const votingProgress = ref({ voted: 0, total: 0 })
    const hasVoted = ref(false)

    const isHost = computed(() => {
        // This needs the current player — resolved in the component via playerStore
        return false
    })

    const playersSortedByScore = computed(() => {
        if (!room.value) { return [] }
        return [...room.value.players].sort((a, b) => {
            const scoreA = room.value?.players.find(p => p.id === a.id) ? 0 : 0
            const scoreB = room.value?.players.find(p => p.id === b.id) ? 0 : 0
            return scoreB - scoreA
        })
    })

    // === Actions ===

    async function loadRoom(code: string): Promise<void> {
        isLoading.value = true
        error.value = null
        try {
            const result = await roomsApi.get(code)
            room.value = result.room
            currentLetter.value = result.room.current_letter
            currentRoundNumber.value = result.room.current_round
            roundEndsAt.value = result.room.round_ends_at
            remainingSeconds.value = result.room.remaining_seconds
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Erreur de chargement'
        } finally {
            isLoading.value = false
        }
    }

    async function joinRoom(code: string): Promise<GameRoom> {
        isLoading.value = true
        try {
            const result = await roomsApi.join(code)
            room.value = result.room
            return result.room
        } finally {
            isLoading.value = false
        }
    }

    async function createRoom(options: {
        total_rounds?: number
        round_duration?: number
        max_players?: number
    }): Promise<GameRoom> {
        isLoading.value = true
        try {
            const result = await roomsApi.create(options)
            room.value = result.room
            return result.room
        } finally {
            isLoading.value = false
        }
    }

    async function leaveRoom(): Promise<void> {
        if (!room.value) { return }
        await roomsApi.leave(room.value.code)
        room.value = null
        resetRound()
    }

    async function startGame(): Promise<void> {
        if (!room.value) { return }
        await roomsApi.start(room.value.code)
    }

    async function submitAnswers(): Promise<void> {
        if (!room.value || hasSubmitted.value) { return }
        await roundsApi.submit(room.value.code, answers.value as Record<string, string>)
        hasSubmitted.value = true
    }

    async function stopRound(): Promise<void> {
        if (!room.value) { return }
        await roundsApi.stop(room.value.code)
    }

    async function submitVotes(myPlayerId: number): Promise<void> {
        if (!room.value || hasVoted.value) { return }
        await votingApi.submit(room.value.code, myVotes.value)
        hasVoted.value = true
    }

    async function forceCloseVoting(): Promise<void> {
        if (!room.value) { return }
        await votingApi.forceClose(room.value.code)
    }

    // === WebSocket event handlers ===

    function onLetterGenerated(payload: WsLetterGeneratedPayload): void {
        currentLetter.value = payload.letter
        currentRoundNumber.value = payload.round_number
        roundEndsAt.value = payload.round_ends_at
        remainingSeconds.value = Math.max(0, Math.round((new Date(payload.round_ends_at).getTime() - Date.now()) / 1000))
        isRoundActive.value = true
        hasSubmitted.value = false
        resetAnswers()
        resetVoting()
        if (room.value) {
            room.value.current_letter = payload.letter
            room.value.current_round = payload.round_number
            room.value.status = 'playing'
        }
    }

    function onScoresCalculated(payload: WsScoresCalculatedPayload): void {
        lastRoundScores.value = payload.scores
        isRoundActive.value = false
        if (room.value) {
            room.value.status = 'scoring'
        }
    }

    function onGameFinished(payload: WsGameFinishedPayload): void {
        gameFinishedData.value = payload
        isRoundActive.value = false
        if (room.value) {
            room.value.status = 'finished'
        }
    }

    function onAchievementUnlocked(payload: WsAchievementUnlockedPayload): void {
        unlockedAchievements.value.push(payload)
        // Auto-remove after 5s
        setTimeout(() => {
            unlockedAchievements.value = unlockedAchievements.value.filter(
                a => a.achievement.key !== payload.achievement.key,
            )
        }, 5000)
    }

    function onVotingStarted(payload: WsVotingStartedPayload, myPlayerId: number): void {
        isVotingPhase.value = true
        hasVoted.value = false
        votingAnswers.value = payload.players_answers
        votingProgress.value = { voted: 0, total: payload.players_answers.length }

        // Pre-fill my votes with auto_valid defaults for other players' answers
        myVotes.value = {}
        for (const pa of payload.players_answers) {
            if (pa.player_id === myPlayerId) { continue }
            myVotes.value[pa.player_id] = {}
            for (const [cat, entry] of Object.entries(pa.answers)) {
                myVotes.value[pa.player_id][cat] = entry.auto_valid
            }
        }

        if (room.value) {
            room.value.status = 'voting'
        }
    }

    function onVoteSubmitted(payload: WsVoteSubmittedPayload): void {
        votingProgress.value = { voted: payload.voted_count, total: payload.total_count }
    }

    function setVote(playerId: number, category: string, isValid: boolean): void {
        if (!myVotes.value[playerId]) { myVotes.value[playerId] = {} }
        myVotes.value[playerId][category] = isValid
    }

    function resetVoting(): void {
        isVotingPhase.value = false
        hasVoted.value = false
        votingAnswers.value = []
        myVotes.value = {}
        votingProgress.value = { voted: 0, total: 0 }
    }

    function syncRemainingSeconds(): void {
        if (roundEndsAt.value) {
            const ms = new Date(roundEndsAt.value).getTime() - Date.now()
            remainingSeconds.value = Math.max(0, Math.round(ms / 1000))
        } else if (remainingSeconds.value > 0) {
            remainingSeconds.value--
        }
    }

    function setAnswer(category: Category, value: string): void {
        answers.value[category] = value
    }

    function resetAnswers(): void {
        const categories: Category[] = ['city', 'country', 'first_name', 'pokemon', 'brand', 'movie']
        categories.forEach(cat => { answers.value[cat] = '' })
    }

    function resetRound(): void {
        currentLetter.value = null
        roundEndsAt.value = null
        isRoundActive.value = false
        hasSubmitted.value = false
        remainingSeconds.value = 0
        lastRoundScores.value = null
        resetAnswers()
        resetVoting()
    }

    function clearAchievement(key: string): void {
        unlockedAchievements.value = unlockedAchievements.value.filter(a => a.achievement.key !== key)
    }

    return {
        room,
        isLoading,
        error,
        currentLetter,
        currentRoundNumber,
        remainingSeconds,
        isRoundActive,
        hasSubmitted,
        answers,
        lastRoundScores,
        gameFinishedData,
        unlockedAchievements,
        isVotingPhase,
        votingAnswers,
        myVotes,
        votingProgress,
        hasVoted,
        isHost,
        playersSortedByScore,
        loadRoom,
        joinRoom,
        createRoom,
        leaveRoom,
        startGame,
        submitAnswers,
        stopRound,
        submitVotes,
        forceCloseVoting,
        onLetterGenerated,
        onScoresCalculated,
        onGameFinished,
        onAchievementUnlocked,
        onVotingStarted,
        onVoteSubmitted,
        setVote,
        resetVoting,
        roundEndsAt,
        syncRemainingSeconds,
        setAnswer,
        resetAnswers,
        resetRound,
        clearAchievement,
    }
})
