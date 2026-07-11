<template>
    <section>
        <!-- Category select -->
        <div v-if="categoriesWithMaterials.length" class="mb-3">
            <label class="block text-[10px] uppercase tracking-[0.3em] text-white/35 font-semibold mb-2">
                Categoría
            </label>
            <div class="relative">
                <select
                    :value="activeCategory?.id ?? ''"
                    :disabled="disabled"
                    class="w-full h-10 appearance-none border border-white/12 bg-[#0D0D0D] pl-4 pr-9 text-xs font-semibold uppercase tracking-[0.15em] text-white outline-none focus:border-white/35 transition disabled:opacity-40 cursor-pointer"
                    @change="setCategory($event.target.value)"
                >
                    <option
                        v-for="category in categoriesWithMaterials"
                        :key="category.id"
                        :value="category.id"
                        class="bg-[#0D0D0D] normal-case tracking-normal"
                    >
                        {{ category.name }} ({{ category.materials?.length || 0 }})
                    </option>
                </select>
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-white/40 text-[10px]">▾</span>
            </div>
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
                        <!-- Skeleton shimmer mientras carga -->
                        <div
                            v-if="!loadedImages.has(material.id)"
                            class="absolute inset-0 skeleton-shimmer"
                        />
                        <img
                            v-if="material.thumbnail_url || material.texture_url"
                            :src="material.thumbnail_url || material.texture_url"
                            :alt="material.name"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                            :class="loadedImages.has(material.id) ? 'opacity-100' : 'opacity-0'"
                            @load="loadedImages.add(material.id)"
                            @error="loadedImages.add(material.id)"
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
                    ? 'Esta categoría no tiene materiales.'
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

const activeCategory = ref(null);

// Tracking de imágenes cargadas para el skeleton
const loadedImages = ref(new Set());

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

const filteredMaterials = computed(() => activeCategory.value?.materials || []);

function setCategory(id) {
    const found = categoriesWithMaterials.value.find((c) => String(c.id) === String(id));
    if (found) activeCategory.value = found;
}

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

/* Shimmer skeleton para thumbnails */
.skeleton-shimmer {
    background: linear-gradient(
        90deg,
        rgba(255,255,255,0.04) 0%,
        rgba(255,255,255,0.10) 40%,
        rgba(255,255,255,0.04) 80%
    );
    background-size: 200% 100%;
    animation: shimmer 1.4s ease-in-out infinite;
}

@keyframes shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>
