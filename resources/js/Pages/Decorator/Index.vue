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

                <p class="mt-5 text-base text-white/50 max-w-xl leading-relaxed">
                    Visualiza mármoles, cuarzos, cuarcitas y piedras naturales en
                    pisos, muros y cubiertas. Selecciona un ambiente para comenzar.
                </p>
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
