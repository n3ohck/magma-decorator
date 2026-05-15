<template>
    <section>
        <div class="mb-3">
            <p class="text-xs uppercase tracking-[0.25em] text-[#9B6A3F] font-semibold">
                Materiales
            </p>

            <h2 class="text-base font-semibold text-[#1F1A17]">
                Elige una superficie
            </h2>

            <p class="text-xs text-[#6B5E55] mt-1">
                Se aplicará sobre la zona seleccionada.
            </p>
        </div>

        <div
            v-if="categoriesWithMaterials.length"
            class="flex gap-2 overflow-x-auto pb-2"
        >
            <button
                v-for="category in categoriesWithMaterials"
                :key="category.id"
                type="button"
                class="px-3 py-2 rounded-full text-xs font-semibold whitespace-nowrap border transition disabled:opacity-50 disabled:cursor-not-allowed"
                :class="activeCategory?.id === category.id
                    ? 'bg-[#1F1A17] text-white border-[#1F1A17]'
                    : 'bg-white text-[#1F1A17] border-black/10 hover:border-[#9B6A3F]'"
                :disabled="disabled"
                @click="activeCategory = category"
            >
                {{ category.name }}
                <span class="opacity-60">
                    {{ category.materials?.length || 0 }}
                </span>
            </button>
        </div>

        <div v-if="categoriesWithMaterials.length" class="mb-3">
            <input
                v-model="search"
                type="text"
                class="w-full h-10 rounded-2xl border border-black/10 px-4 text-sm outline-none focus:border-[#9B6A3F] disabled:opacity-50"
                placeholder="Buscar material..."
                :disabled="disabled"
            />
        </div>

        <div
            v-if="filteredMaterials.length"
            class="grid grid-cols-1 gap-2 max-h-[260px] overflow-y-auto pr-1"
        >
            <button
                v-for="material in filteredMaterials"
                :key="material.id"
                type="button"
                class="group text-left rounded-2xl overflow-hidden border bg-white transition disabled:cursor-not-allowed disabled:opacity-60"
                :class="Number(activeMaterialId) === Number(material.id)
                    ? 'border-[#9B6A3F] shadow-md ring-2 ring-[#9B6A3F]/10'
                    : 'border-black/10 hover:shadow-md hover:border-[#9B6A3F]'"
                :disabled="disabled"
                @click="selectMaterial(material)"
            >
                <div class="grid grid-cols-[70px_1fr] gap-3 p-2">
                    <div class="relative h-16 w-16 rounded-xl bg-stone-100 overflow-hidden">
                        <img
                            v-if="material.thumbnail_url || material.texture_url"
                            :src="material.thumbnail_url || material.texture_url"
                            :alt="material.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                        />

                        <div
                            v-else
                            class="w-full h-full grid place-items-center text-xs text-[#6B5E55]"
                        >
                            Sin imagen
                        </div>

                        <transition name="soft-fade">
                            <div
                                v-if="Number(activeMaterialId) === Number(material.id)"
                                class="absolute inset-0 bg-[#1F1A17]/20 grid place-items-center"
                            >
                                <span class="h-6 w-6 rounded-full bg-white text-[#1F1A17] grid place-items-center text-xs font-bold">
                                    ✓
                                </span>
                            </div>
                        </transition>
                    </div>

                    <div class="min-w-0 flex flex-col justify-center">
                        <p class="text-sm font-bold text-[#1F1A17] leading-tight truncate">
                            {{ material.name }}
                        </p>

                        <p class="text-xs text-[#6B5E55] mt-1 truncate">
                            {{ activeCategory?.name || 'Material' }}
                        </p>

                        <p class="text-xs text-[#9B6A3F] mt-1 truncate">
                            {{ material.finish || 'Sin acabado' }}
                            <span v-if="material.base_color">
                                · {{ material.base_color }}
                            </span>
                        </p>
                    </div>
                </div>
            </button>
        </div>

        <div
            v-else-if="categoriesWithMaterials.length"
            class="rounded-2xl border border-dashed border-black/10 p-4 text-sm text-[#6B5E55]"
        >
            No hay materiales que coincidan con la búsqueda.
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-black/10 p-4 text-sm text-[#6B5E55]"
        >
            No hay materiales activos disponibles.
        </div>
    </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    activeMaterialId: {
        type: [Number, String, null],
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['select']);

const search = ref('');
const activeCategory = ref(null);

const categoriesWithMaterials = computed(() => {
    return (props.categories || []).filter((category) => {
        return Array.isArray(category.materials) && category.materials.length > 0;
    });
});

watch(
    categoriesWithMaterials,
    (categories) => {
        if (!categories.length) {
            activeCategory.value = null;
            return;
        }

        const currentExists = categories.some((category) => {
            return category.id === activeCategory.value?.id;
        });

        if (!activeCategory.value || !currentExists) {
            activeCategory.value = categories[0];
        }
    },
    { immediate: true }
);

const filteredMaterials = computed(() => {
    const materials = activeCategory.value?.materials || [];

    if (!search.value.trim()) {
        return materials;
    }

    const term = search.value.toLowerCase();

    return materials.filter((material) => {
        return [
            material.name,
            material.slug,
            material.sku,
            material.finish,
            material.base_color,
            material.short_description,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term));
    });
});

function selectMaterial(material) {
    if (props.disabled) return;

    emit('select', material);
}
</script>

<style scoped>
.soft-fade-enter-active,
.soft-fade-leave-active {
    transition: opacity 180ms ease;
}

.soft-fade-enter-from,
.soft-fade-leave-to {
    opacity: 0;
}
</style>
