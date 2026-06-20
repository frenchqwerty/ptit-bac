<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePlayerStore } from '@/stores/playerStore'

const playerStore = usePlayerStore()
const playerName = ref('')
const error = ref('')

onMounted(async () => {
    if (playerStore.token) {
        const room = await playerStore.loadMe()
        if (playerStore.isAuthenticated) {
            if (room) {
                const path = room.status === 'playing' || room.status === 'scoring'
                    ? `/game/${room.code}`
                    : `/room/${room.code}`
                router.visit(path)
            } else {
                router.visit('/lobby')
            }
        }
    }
})

async function handleLogin(): Promise<void> {
    if (!playerName.value.trim()) {
        error.value = 'Entre ton prénom pour jouer!'
        return
    }
    error.value = ''
    try {
        await playerStore.login(playerName.value.trim())
        router.visit('/lobby')
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Erreur de connexion'
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full space-y-8">
            <!-- Title -->
            <div class="text-center space-y-4">
                <div class="text-6xl mb-4 animate-bounce">⚡</div>
                <h1 class="retro-title text-3xl text-neon-purple text-glow-purple leading-relaxed">
                    BAC<br>ATTACK
                </h1>
                <p class="text-retro-muted retro-title text-xs leading-relaxed">
                    LE PETIT BAC<br>MULTIJOUEUR
                </p>
                <div class="flex justify-center gap-2 mt-2">
                    <span class="category-badge">VILLE</span>
                    <span class="category-badge">PAYS</span>
                    <span class="category-badge">PRÉNOM</span>
                </div>
                <div class="flex justify-center gap-2">
                    <span class="category-badge">POKÉMON</span>
                    <span class="category-badge">MARQUE</span>
                    <span class="category-badge">FILM</span>
                </div>
            </div>

            <!-- Login Form -->
            <div class="retro-card pixel-border p-6 space-y-4">
                <p class="retro-title text-xs text-neon-cyan text-center">ENTREZ DANS L'ARENE</p>

                <div>
                    <label class="retro-title text-xs text-retro-muted block mb-2">
                        TON PRÉNOM
                    </label>
                    <input
                        v-model="playerName"
                        class="pixel-input"
                        type="text"
                        placeholder="Thomas, Sarah, Lucas..."
                        maxlength="50"
                        autocomplete="off"
                        @keyup.enter="handleLogin"
                    />
                </div>

                <p v-if="error" class="retro-title text-xs text-neon-red pixel-blink">
                    ⚠ {{ error }}
                </p>

                <button
                    class="btn-retro w-full text-center"
                    :disabled="playerStore.isLoading"
                    @click="handleLogin"
                >
                    <span v-if="playerStore.isLoading" class="pixel-blink">CHARGEMENT...</span>
                    <span v-else>▶ JOUER</span>
                </button>

                <p class="text-center retro-title text-xs text-retro-muted">
                    JUSQU'À 10 JOUEURS · 6 CATÉGORIES
                </p>
            </div>

            <!-- Footer -->
            <p class="text-center text-retro-muted text-xs retro-title">
                © BACATTACK 2026 · ALL RIGHTS RESERVED
            </p>
        </div>
    </div>
</template>
