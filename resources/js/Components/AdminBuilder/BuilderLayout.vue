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

                <nav class="flex-1 p-4 space-y-2">
                    <a
                        v-for="item in menu"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                        :class="isActive(item.href)
                            ? 'bg-violet-500 text-white shadow-lg shadow-violet-500/20'
                            : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    >
                        <span>{{ item.icon }}</span>
                        <span>{{ item.label }}</span>
                    </a>
                </nav>

                <!-- Logout -->
                <div class="p-4 border-t border-white/10">
                    <a
                        href="/admin/logout"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-white/50 hover:bg-white/8 hover:text-white transition w-full"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    >
                        <span>🚪</span>
                        <span>Cerrar sesión</span>
                    </a>
                    <form id="logout-form" action="/admin/logout" method="POST" class="hidden">
                        <input type="hidden" name="_token" :value="csrfToken" />
                    </form>
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
                            href="/admin"
                            class="rounded-2xl border border-white/10 px-4 py-2 text-sm text-white/80 hover:bg-white/10"
                        >
                            Volver a Backpack
                        </a>
                    </div>

                    <nav class="lg:hidden px-5 pb-4 flex gap-2 overflow-x-auto">
                        <a
                            v-for="item in menu"
                            :key="item.href"
                            :href="item.href"
                            class="rounded-full px-4 py-2 text-sm whitespace-nowrap"
                            :class="isActive(item.href)
                                ? 'bg-violet-500 text-white'
                                : 'bg-white/10 text-white/70'"
                        >
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
defineProps({
    title: {
        type: String,
        default: 'Builder',
    },
});

const menu = [
    { label: 'Dashboard',   href: '/admin/builder',                    icon: '✨' },
    { label: 'Categorías',  href: '/admin/builder/material-categories', icon: '📁' },
    { label: 'Materiales',  href: '/admin/builder/materials',           icon: '💎' },
    { label: 'Ambientes',   href: '/admin/builder/environments',        icon: '🖼️' },
    { label: 'Zonas',       href: '/admin/builder/environment-zones',   icon: '🎯' },
    // { label: 'Leads', href: '/admin/builder/leads', icon: '👤' },
];

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

function isActive(href) {
    return window.location.pathname === href;
}
</script>
