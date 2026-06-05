<template>
    <div class="min-h-screen bg-[#0D0B10] text-[#EDE7F6]">
        <div class="flex">
            <aside class="hidden lg:flex lg:w-72 h-screen sticky top-0 border-r border-white/10 bg-[#15111D] flex-col overflow-y-auto">
                <div class="p-6 border-b border-white/10">
                    <p class="text-xs uppercase tracking-[0.28em] text-violet-300">
                        Magma
                    </p>
                    <h1 class="mt-2 text-xl font-bold">
                        Decorador Builder
                    </h1>
                </div>

                <nav class="flex-1 p-4 space-y-1">
                    <a
                        v-for="item in menu"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                        :class="isActive(item.href)
                            ? 'bg-violet-500 text-white shadow-lg shadow-violet-500/20'
                            : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    >
                        <component :is="item.icon" class="h-5 w-5 shrink-0" />
                        <span>{{ item.label }}</span>
                    </a>
                </nav>

                <!-- Logout -->
                <div class="p-4 border-t border-white/10">
                    <a
                        href="/admin/do-logout"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-white/50 hover:bg-white/8 hover:text-white transition w-full"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                        </svg>
                        <span>Cerrar sesión</span>
                    </a>
                </div>
            </aside>

            <main class="flex-1 min-w-0">
                <header class="sticky top-0 z-20 bg-[#0D0B10]/90 backdrop-blur border-b border-white/10">
                    <div class="px-5 md:px-8 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-violet-300">
                                Panel administrativo
                            </p>
                            <h2 class="text-2xl font-bold">
                                {{ title }}
                            </h2>
                        </div>

                        <a
                            href="/admin/do-logout"
                            class="rounded-2xl border border-white/10 px-4 py-2 text-sm text-white/80 hover:bg-white/10 flex items-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                            </svg>
                            Cerrar sesión
                        </a>
                    </div>

                    <nav class="lg:hidden px-5 pb-4 flex gap-2 overflow-x-auto">
                        <a
                            v-for="item in menu"
                            :key="item.href"
                            :href="item.href"
                            class="rounded-full px-4 py-2 text-sm whitespace-nowrap flex items-center gap-2"
                            :class="isActive(item.href)
                                ? 'bg-violet-500 text-white'
                                : 'bg-white/10 text-white/70'"
                        >
                            <component :is="item.icon" class="h-4 w-4 shrink-0" />
                            {{ item.label }}
                        </a>
                    </nav>
                </header>

                <section class="p-5 md:p-8">
                    <slot />
                </section>
            </main>
        </div>
    </div>
</template>

<script setup>
import { h } from 'vue';

defineProps({
    title: { type: String, default: 'Builder' },
});

// ── Heroicons (outline, stroke-width 1.5) ─────────────────────────────────────

const IconSparkles = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z' }),
]) };

const IconFolder = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z' }),
]) };

const IconGem = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z' }),
]) };

const IconPhoto = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z' }),
]) };

const IconSquares = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z' }),
]) };

const menu = [
    { label: 'Dashboard',  href: '/admin/builder',                    icon: IconSparkles },
    { label: 'Categorías', href: '/admin/builder/material-categories', icon: IconFolder   },
    { label: 'Materiales', href: '/admin/builder/materials',           icon: IconGem      },
    { label: 'Ambientes',  href: '/admin/builder/environments',        icon: IconPhoto    },
    { label: 'Zonas',      href: '/admin/builder/environment-zones',   icon: IconSquares  },
];

function isActive(href) {
    return window.location.pathname === href;
}
</script>
