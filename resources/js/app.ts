import { createInertiaApp } from '@inertiajs/vue3'
import { configureEcho } from '@laravel/echo-vue'
import { createPinia } from 'pinia'
import { createApp, type DefineComponent, h } from 'vue'

// Configure Laravel Echo with Reverb
configureEcho({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel: { name: string }) => ({
        authorize: (socketId: string, callback: (error: Error | null, data: unknown) => void) => {
            const token = localStorage.getItem('player_token')
            const xsrfToken = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '')
            fetch('/broadcasting/auth', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-XSRF-TOKEN': xsrfToken,
                },
                body: JSON.stringify({ socket_id: socketId, channel_name: channel.name }),
            })
                .then((res) => res.json())
                .then((data) => callback(null, data))
                .catch((err: Error) => callback(err, null))
        },
    }),
})

const appName = import.meta.env.VITE_APP_NAME || 'BacAttack'
const pinia = createPinia()

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#7C3AED',
    },
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { eager: true })
        const page = pages[`./pages/${name}.vue`]
        if (!page) { throw new Error(`Page not found: ${name}`) }
        return page
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el)
    },
})

