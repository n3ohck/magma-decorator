<template>
    <div class="min-h-screen bg-[#0D0D0D]">

        <!-- Header -->
        <header class="border-b border-white/8 px-6 py-5">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="/" class="shrink-0">
                    <img
                        :src="'/images/magma-logo.png'"
                        alt="Magma Superficies"
                        class="h-8 w-auto object-contain"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block'"
                    />
                    <span class="hidden text-sm font-bold uppercase tracking-[0.2em] text-white">
                        Magma Superficies
                    </span>
                </a>

                <a href="/" class="text-white/40 text-xs uppercase tracking-[0.25em] hover:text-white/70 transition">
                    ← Volver al sitio
                </a>
            </div>
        </header>

        <section class="max-w-7xl mx-auto px-6 py-16 md:py-24">

            <!-- Hero text -->
            <div class="max-w-3xl">
                <p class="text-xs uppercase tracking-[0.4em] text-[#CC1A1A] font-semibold">
                    Decorador Virtual
                </p>

                <h1 class="mt-4 text-4xl md:text-6xl font-bold text-white leading-tight uppercase tracking-tight">
                    Diseña tu espacio<br />con materiales reales
                </h1>

                <p class="mt-6 text-xl md:text-2xl text-white/80 max-w-xl leading-snug font-light">
                    Visualiza espacios con <span class="relative font-medium text-white whitespace-nowrap">materiales reales<span class="absolute -bottom-1 left-0 h-px w-full bg-gradient-to-r from-[#CC1A1A] to-transparent"></span></span>.
                </p>

                <p class="mt-4 text-sm text-white/40 max-w-xl leading-relaxed">
                    Aplica mármoles, cuarzos, cuarcitas y piedras naturales sobre
                    pisos, muros y cubiertas, y observa el resultado al instante.
                </p>

                <!-- Call to action / guía -->
                <div class="mt-7 inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/[0.04] pl-4 pr-5 py-2.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#CC1A1A] opacity-70"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#CC1A1A]"></span>
                    </span>
                    <span class="text-sm font-medium text-white/90 tracking-wide">
                        Selecciona un ambiente para comenzar
                    </span>
                    <span class="text-white/40 animate-bounce">↓</span>
                </div>
            </div>

            <!-- Divider -->
            <div class="mt-14 mb-10 flex items-center gap-4">
                <span class="text-xs uppercase tracking-[0.3em] text-white/30">Ambientes disponibles</span>
                <div class="flex-1 h-px bg-white/8"></div>
                <span class="text-xs text-white/20">{{ environments.length }}</span>
            </div>

            <!-- Grid -->
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
                <a
                    v-for="environment in environments"
                    :key="environment.id"
                    :href="`/decorador/${environment.slug}`"
                    class="group relative bg-[#161616] border border-white/8 overflow-hidden hover:border-white/20 transition-all duration-300"
                >
                    <!-- Image -->
                    <div class="aspect-[4/3] overflow-hidden bg-black/40">
                        <img
                            :src="environment.preview_image_url"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-700 brightness-90 group-hover:brightness-100"
                            :alt="environment.name"
                        />
                    </div>

                    <!-- Info -->
                    <div class="p-5 flex items-end justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.3em] text-white/35">
                                {{ environment.type || 'Ambiente' }}
                            </p>

                            <h2 class="mt-1 text-lg font-bold text-white uppercase tracking-wide truncate">
                                {{ environment.name }}
                            </h2>

                            <p v-if="environment.description" class="mt-1 text-xs text-white/40 line-clamp-1">
                                {{ environment.description }}
                            </p>
                        </div>

                        <!-- Arrow -->
                        <span class="shrink-0 h-10 w-10 border border-white/15 flex items-center justify-center text-white/40 group-hover:bg-[#CC1A1A] group-hover:border-[#CC1A1A] group-hover:text-white transition-all duration-300">
                            →
                        </span>
                    </div>

                    <!-- Zones badge -->
                    <div
                        v-if="environment.zones?.length"
                        class="absolute top-3 left-3 bg-black/60 backdrop-blur px-2.5 py-1 text-[10px] uppercase tracking-[0.2em] text-white/60"
                    >
                        {{ environment.zones.length }} zona{{ environment.zones.length !== 1 ? 's' : '' }}
                    </div>
                </a>
            </div>

        </section>
    </div>
</template>

<script setup>
defineProps({
    environments: {
        type: Array,
        required: true,
    },
});
</script>
