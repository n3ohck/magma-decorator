<template>
    <section>
        <!-- Category tabs -->
        <div
            v-if="categoriesWithMaterials.length"
            class="flex gap-1.5 overflow-x-auto pb-2 mb-3"
        >
            <button
                v-for="category in categoriesWithMaterials"
                :key="category.id"
                type="button"
                class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] whitespace-nowrap border transition disabled:opacity-40 disabled:cursor-not-allowed"
                :class="activeCategory?.id === category.id
                    ? 'bg-[#CC1A1A] text-white border-[#CC1A1A]'
                    : 'bg-transparent text-white/50 border-white/15 hover:border-white/35 hover:text-white'"
                :disabled="disabled"
                @click="activeCategory = category"
            >
                {{ category.name }}
                <span class="ml-1 opacity-50">{{ category.materials?.length || 0 }}</span>
            </button>
        </div>

        <!-- Search -->
        <div v-if="categoriesWithMaterials.length" class="mb-3">
            <input
                v-model="search"
                type="text"
                class="w-full h-9 border border-white/10 bg-white/[0.04] px-4 text-xs text-white placeholder:text-white/25 outline-none focus:border-white/30 transition disabled:opacity-40"
                placeholder="Buscar material…"
                :disabled="disabled"
            />
        </div>

        <!-- Material list -->
        <div
            v-if="filteredMaterials.length"
            class="flex flex-col gap-1 max-h-[300px] overflow-y-auto pr-0.5"
        >
            <button
                v-for="material in filteredMaterials"
                :key="material.id"
                type="button"
                class="group text-left border bg-white/[0.03] transition disabled:cursor-not-allowed disabled:opacity-40"
                :class="Number(activeMaterialId) === Number(material.id)
                    ? 'border-[#CC1A1A] bg-[#CC1A1A]/5'
                    : 'border-white/8 hover:border-white/25 hover:bg-white/[0.06]'"
                :disabled="disabled"
                @click="selectMaterial(material)"
            >
                <div class="grid grid-cols-[64px_1fr] gap-3 p-2">
                    <!-- Thumbnail -->
                    <div class="relative h-14 w-14 bg-black/40 overflow-hidden">
                        <img
                            v-if="material.thumbnail_url || material.texture_url"
                            :src="material.thumbnail_url || material.texture_url"
                            :alt="material.name"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                        />
                        <div
                            v-else
                            class="w-full h-full grid place-items-center text-[10px] text-white/20"
                        >
                            —
                        </div>

                        <!-- Active check -->
                        <transition name="soft-fade">
                            <div
                                v-if="Number(activeMaterialId) === Number(material.id)"
                                class="absolute inset-0 bg-[#CC1A1A]/30 grid place-items-center"
                            >
                                <span class="h-5 w-5 bg-[#CC1A1A] text-white grid place-items-center text-[10px] font-bold">
                                    ✓
                                </span>
                            </div>
                        </transition>
                    </div>

                    <!-- Info -->
                    <div class="min-w-0 flex flex-col justify-center">
                        <p class="text-sm font-bold text-white leading-tight truncate">
                            {{ material.name }}
                        </p>

                        <p class="text-[10px] uppercase tracking-[0.15em] text-white/35 mt-1 truncate">
                            {{ activeCategory?.name || 'Material' }}
                        </p>

                        <p
                            v-if="material.finish || material.base_color"
                            class="text-[10px] text-[#CC1A1A]/70 mt-0.5 truncate"
                        >
                            {{ material.finish }}
                            <span v-if="material.finish && material.base_color"> · </span>
                            {{ material.base_color }}
                        </p>
                    </div>
                </div>
            </button>
        </div>

        <div
            v-else
            class="border border-dashed border-white/10 p-4 text-xs text-white/30"
        >
            {{
                categoriesWithMaterials.length
                    ? 'Sin resultados para esa búsqueda.'
                    : 'No hay materiales activos disponibles.'
            }}
        </div>
    </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    activeMaterialId: { type: [Number, String, null], default: null },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['select']);

const search = ref('');
const activeCategory = ref(null);

const categoriesWithMaterials = computed(() =>
    (props.categories || []).filter(
        (c) => Array.isArray(c.materials) && c.materials.length > 0
    )
);

watch(
    categoriesWithMaterials,
    (cats) => {
        if (!cats.length) { activeCategory.value = null; return; }
        const exists = cats.some((c) => c.id === activeCategory.value?.id);
        if (!activeCategory.value || !exists) activeCategory.value = cats[0];
    },
    { immediate: true }
);

const filteredMaterials = computed(() => {
    const list = activeCategory.value?.materials || [];
    if (!search.value.trim()) return list;
    const term = search.value.toLowerCase();
    return list.filter((m) =>
        [m.name, m.slug, m.sku, m.finish, m.base_color, m.short_description]
            .filter(Boolean)
            .some((v) => String(v).toLowerCase().includes(term))
    );
});

function selectMaterial(material) {
    if (props.disabled) return;
    emit('select', material);
}
</script>

<style scoped>
.soft-fade-enter-active,
.soft-fade-leave-active { transition: opacity 150ms ease; }
.soft-fade-enter-from,
.soft-fade-leave-to { opacity: 0; }
</style>
