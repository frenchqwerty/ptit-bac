<script setup lang="ts">
defineProps<{
    title: string
    show: boolean
}>()

const emit = defineEmits<{ close: [] }>()
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center"
                @click.self="emit('close')"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" />

                <!-- Modal -->
                <div class="relative retro-card pixel-border max-w-lg w-full mx-4 p-6 z-10">
                    <div class="flex items-center justify-between mb-4 border-b border-retro-border pb-3">
                        <h2 class="retro-title text-sm text-neon-purple">{{ title }}</h2>
                        <button
                            class="text-retro-muted hover:text-white transition-colors text-xl"
                            @click="emit('close')"
                        >
                            ✕
                        </button>
                    </div>
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active {
    transition: all 0.2s ease;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
