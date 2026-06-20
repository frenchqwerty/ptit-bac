<script setup lang="ts">
import type { GameRoom } from '@/types/game'

defineProps<{
    room: GameRoom
}>()

defineEmits<{ join: [code: string] }>()
</script>

<template>
    <div class="retro-card pixel-border p-4 space-y-3 hover:glow-purple transition-all cursor-pointer">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <span class="retro-title text-sm text-neon-cyan">{{ room.code }}</span>
            <span
                :class="[
                    'retro-title text-xs px-2 py-1',
                    room.status === 'waiting' ? 'text-neon-green' : 'text-neon-yellow',
                ]"
            >
                {{ room.status_label }}
            </span>
        </div>

        <!-- Host -->
        <p class="text-retro-muted text-sm">
            Hôte: <span class="text-white">{{ room.host?.display_name ?? '—' }}</span>
        </p>

        <!-- Players count -->
        <div class="flex items-center justify-between">
            <div class="pixel-progress flex-1 mr-4">
                <div
                    class="pixel-progress-fill bg-neon-purple"
                    :style="{ width: `${(room.players_count / room.max_players) * 100}%` }"
                />
            </div>
            <span class="retro-title text-xs text-retro-muted whitespace-nowrap">
                {{ room.players_count }}/{{ room.max_players }}
            </span>
        </div>

        <!-- Rounds info -->
        <p class="text-retro-muted text-xs">{{ room.total_rounds }} manches · {{ room.round_duration }}s</p>

        <!-- Join button -->
        <button
            v-if="room.status === 'waiting'"
            class="btn-retro btn-retro-cyan w-full mt-2"
            @click.stop="$emit('join', room.code)"
        >
            ▶ REJOINDRE
        </button>
    </div>
</template>
