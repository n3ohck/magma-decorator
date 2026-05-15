<template>
    <div class="min-h-screen bg-[#F5F2EC]">
        <section class="max-w-7xl mx-auto px-6 py-12">
            <div class="max-w-3xl">
                <p class="text-xs uppercase tracking-[0.35em] text-[#9B6A3F] font-semibold">
                    Magma Superficies
                </p>

                <h1 class="mt-4 text-4xl md:text-6xl font-semibold text-[#1F1A17] leading-tight">
                    Diseña tu espacio con materiales reales
                </h1>

                <p class="mt-5 text-lg text-[#6B5E55]">
                    Explora ambientes cuidadosamente seleccionados y visualiza mármoles,
                    cuarzos, cuarcitas y piedras naturales en pisos, muros y cubiertas.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mt-12">
                <a
                    v-for="environment in environments"
                    :key="environment.id"
                    :href="`/decorador/${environment.slug}`"
                    class="group bg-white rounded-3xl overflow-hidden shadow-sm border border-black/5 hover:shadow-xl transition"
                >
                    <div class="aspect-[4/3] overflow-hidden bg-stone-100">
                        <img
                            :src="environment.preview_image_url"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            :alt="environment.name"
                        />
                    </div>

                    <div class="p-5">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#9B6A3F]">
                            {{ environment.type || 'Ambiente' }}
                        </p>

                        <h2 class="mt-2 text-xl font-semibold text-[#1F1A17]">
                            {{ environment.name }}
                        </h2>

                        <p class="mt-2 text-sm text-[#6B5E55] line-clamp-2">
                            {{ environment.description }}
                        </p>
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
