// BacAttack API Client
// Centralized HTTP calls with player token auth

import type { Achievement, EloHistoryEntry, GameHistory, GameRoom, Player, PlayerStatistic } from '@/types/game'

const BASE_URL = '/api'

function getToken(): string | null {
    return localStorage.getItem('player_token')
}

async function request<T>(
    method: string,
    path: string,
    body?: unknown,
): Promise<T> {
    const token = getToken()
    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    }

    if (token) {
        headers['Authorization'] = `Bearer ${token}`
    }

    const res = await fetch(`${BASE_URL}${path}`, {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
    })

    if (!res.ok) {
        const error = await res.json().catch(() => ({ message: 'Unknown error' }))
        throw new Error(error.message || `HTTP ${res.status}`)
    }

    return res.json() as Promise<T>
}

// Auth
export const authApi = {
    login: (name: string) =>
        request<{ player: Player; token: string }>('POST', '/auth/login', { name }),
    me: () => request<{ player: Player; current_room: GameRoom | null }>('GET', '/auth/me'),
    logout: () => request<{ message: string }>('POST', '/auth/logout'),
}

// Game Rooms
export const roomsApi = {
    list: () => request<{ rooms: GameRoom[] }>('GET', '/rooms'),
    get: (code: string) => request<{ room: GameRoom }>('GET', `/rooms/${code}`),
    create: (data: { total_rounds?: number; round_duration?: number; max_players?: number; categories?: string[] }) =>
        request<{ room: GameRoom }>('POST', '/rooms', data),
    join: (code: string) => request<{ room: GameRoom }>('POST', `/rooms/${code}/join`),
    leave: (code: string) => request<{ message: string }>('POST', `/rooms/${code}/leave`),
    start: (code: string) => request<{ message: string; letter: string; round_number: number }>('POST', `/rooms/${code}/start`),
}

// Rounds
export const roundsApi = {
    submit: (code: string, answers: Record<string, string>) =>
        request<{ message: string }>('POST', `/rooms/${code}/submit`, { answers }),
    stop: (code: string) =>
        request<{ message: string; letter?: string; round_number?: number }>('POST', `/rooms/${code}/stop`),
}

// Voting
export const votingApi = {
    submit: (code: string, votes: Record<string, Record<string, boolean>>) =>
        request<{ message: string }>('POST', `/rooms/${code}/vote`, { votes }),
    forceClose: (code: string) =>
        request<{ message: string }>('POST', `/rooms/${code}/vote/force-close`),
}

// Leaderboard
export const leaderboardApi = {
    index: () => request<{ leaderboard: Player[] }>('GET', '/leaderboard'),
}

// Profile
export const profileApi = {
    show: () =>
        request<{
            player: Player
            statistics: PlayerStatistic
            achievements: Achievement[]
            elo_history: EloHistoryEntry[]
        }>('GET', '/profile'),
    history: (page = 1) =>
        request<{ data: GameHistory[]; current_page: number; last_page: number }>(
            'GET',
            `/profile/history?page=${page}`,
        ),
}
