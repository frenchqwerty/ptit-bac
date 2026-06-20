// BacAttack — Shared TypeScript Types

export interface Player {
    id: number
    uuid: string
    name: string
    display_name: string
    avatar_color: string
    elo_rating: number
    best_elo: number
    is_online: boolean
    win_rate: number
    average_score: number
    statistic?: PlayerStatistic
    achievements_count?: number
    joined_at?: string
}

export interface PlayerStatistic {
    games_played: number
    games_won: number
    best_score: number
    total_score: number
    total_rounds_played: number
    unique_answers_found: number
    perfect_rounds: number
    current_win_streak: number
    best_win_streak: number
    total_answers_submitted: number
    stop_rounds_called: number
}

export type GameStatus = 'waiting' | 'countdown' | 'playing' | 'voting' | 'scoring' | 'finished'
export type Category = 'city' | 'country' | 'first_name' | 'pokemon' | 'brand' | 'movie'

export interface CategoryAnswer {
    value: string
    valid: boolean
    unique: boolean
    points: number
}

export interface ScoreResult {
    round_score: number
    unique_count: number
    has_perfect: boolean
    categories: Record<Category, CategoryAnswer>
}

export interface GameRoom {
    id: number
    code: string
    status: GameStatus
    status_label: string
    current_letter: string | null
    current_round: number
    total_rounds: number
    round_duration: number
    max_players: number
    players_count: number
    categories: Category[]
    categories_labels: Record<Category, string>
    round_ends_at: string | null
    remaining_seconds: number
    host: Player | null
    players: Player[]
    created_at: string
}

export interface Round {
    id: number
    game_room_id: number
    round_number: number
    letter: string
    status: 'playing' | 'scoring' | 'finished'
    stopped_by_player_id: number | null
    started_at: string | null
    stopped_at: string | null
    finished_at: string | null
}

export interface Achievement {
    key: string
    name: string
    description: string
    icon: string
    category: string
    reward_elo: number
    unlocked_at?: string
}

export interface EloHistoryEntry {
    elo_before: number
    elo_after: number
    elo_change: number
    position: number | null
    total_players: number | null
    created_at: string
}

export interface GameHistory {
    id: number
    game_room_id: number
    winner_id: number | null
    players_data: Array<{ id: number; name: string; score: number; position: number }>
    rounds_data: Array<{ number: number; letter: string; duration: number }>
    letters_used: string[]
    rounds_played: number
    highest_score: number
    started_at: string | null
    finished_at: string | null
}

// WebSocket Event Payloads
export interface WsPlayerJoinedPayload {
    player: Omit<Player, 'statistic'>
    players_count: number
}

export interface WsLetterGeneratedPayload {
    letter: string
    round_number: number
    total_rounds: number
    round_ends_at: string
    duration_seconds: number
}

export interface WsScoresCalculatedPayload {
    round_number: number
    letter: string
    scores: Record<string, ScoreResult>
}

export interface WsGameFinishedPayload {
    winner: { id: number; name: string; score: number; position: number } | null
    players: Array<{ id: number; name: string; score: number; position: number }>
    highest_score: number
    rounds_played: number
}

export interface WsAchievementUnlockedPayload {
    player_id: number
    player_name: string
    achievement: Pick<Achievement, 'key' | 'name' | 'description' | 'icon'>
}

// Voting

export interface VotingAnswerEntry {
    value: string
    auto_valid: boolean
}

export interface VotingPlayerAnswer {
    player_id: number
    display_name: string
    avatar_color: string
    answers: Record<Category, VotingAnswerEntry>
}

export interface WsVotingStartedPayload {
    round_number: number
    letter: string
    players_answers: VotingPlayerAnswer[]
}

export interface WsVoteSubmittedPayload {
    voter_id: number
    voter_name: string
    voted_count: number
    total_count: number
}
