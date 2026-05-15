<template>
    <div class="h-screen overflow-hidden bg-[#F5F2EC]">
        <div class="grid h-full overflow-hidden lg:grid-cols-[minmax(0,1fr)_420px] xl:grid-cols-[minmax(0,1fr)_460px]">
            <!-- Área principal -->
            <main class="min-w-0 h-full overflow-hidden p-3 md:p-5 flex flex-col">
                <!-- Header compacto -->
                <header class="shrink-0 mb-3 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div class="min-w-0">
                        <a href="/decorador" class="text-xs md:text-sm text-[#9B6A3F] hover:underline">
                            ← Cambiar ambiente
                        </a>

                        <h1 class="mt-1 text-2xl md:text-3xl font-semibold text-[#1F1A17] leading-tight">
                            {{ environment.name }}
                        </h1>

                        <p class="text-sm text-[#6B5E55] mt-1">
                            Selecciona una zona y después elige un material.
                        </p>
                    </div>

                    <div class="hidden md:block rounded-2xl bg-white border border-black/5 px-4 py-2 shadow-sm">
                        <p class="text-[10px] uppercase tracking-[0.2em] text-[#9B6A3F] font-semibold">
                            Zona activa
                        </p>

                        <p class="text-sm font-semibold text-[#1F1A17]">
                            {{ selectedZone?.name || 'Sin zona seleccionada' }}
                        </p>
                    </div>
                </header>

                <section class="min-h-0 flex-1 overflow-hidden">
                    <div class="h-full min-h-0">
                        <DecoratorCanvas
                            :environment="environment"
                            :selected-zone="selectedZone"
                            :selected-materials="selectedMaterials"
                            @applying-change="isApplyingMaterial = $event"
                        />
                    </div>
                </section>

                <!-- Resumen compacto inferior -->
                <section class="shrink-0 mt-3 rounded-2xl bg-white border border-black/5 px-4 py-3 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-[#9B6A3F] font-semibold">
                                Diseño actual
                            </p>

                            <p v-if="hasAppliedMaterials" class="text-sm text-[#1F1A17] font-semibold truncate">
                                {{ appliedMaterialsList.length }} material(es) aplicado(s)
                            </p>

                            <p v-else class="text-sm text-[#6B5E55] truncate">
                                Aún no has aplicado materiales.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="text-sm text-[#9B6A3F] hover:underline disabled:opacity-40 disabled:no-underline shrink-0"
                            :disabled="!hasAppliedMaterials || isApplyingMaterial"
                            @click="clearMaterials"
                        >
                            Limpiar
                        </button>
                    </div>

                    <div v-if="hasAppliedMaterials" class="mt-2 flex gap-2 overflow-x-auto pb-1">
                        <div
                            v-for="item in appliedMaterialsList"
                            :key="item.zone.id"
                            class="shrink-0 rounded-xl border border-black/5 bg-[#F8F5EF] px-3 py-2 flex items-center gap-2 max-w-[240px]"
                        >
                            <img
                                v-if="item.material.thumbnail_url || item.material.texture_url"
                                :src="item.material.thumbnail_url || item.material.texture_url"
                                class="h-8 w-8 rounded-lg object-cover bg-stone-100"
                                :alt="item.material.name"
                            />

                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-[#1F1A17] truncate">
                                    {{ item.zone.name }}
                                </p>

                                <p class="text-xs text-[#6B5E55] truncate">
                                    {{ item.material.name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!-- Sidebar -->
            <aside class="h-full min-h-0 overflow-y-auto bg-white border-l border-black/5">
                <div class="p-4 md:p-5">
                    <div class="sticky top-0 bg-white z-10 pb-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-[#9B6A3F] font-semibold">
                            Configurador
                        </p>

                        <h2 class="text-xl md:text-2xl font-semibold text-[#1F1A17] leading-tight">
                            Personaliza tu espacio
                        </h2>

                        <p class="text-sm text-[#6B5E55] mt-1">
                            Primero selecciona una zona y luego un material.
                        </p>
                    </div>

                    <!-- Paso 1 -->
                    <section>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="h-7 w-7 rounded-full bg-[#1F1A17] text-white text-sm grid place-items-center font-semibold">
                                1
                            </span>

                            <h3 class="font-semibold text-[#1F1A17]">
                                Selecciona zona
                            </h3>
                        </div>

                        <ZoneSelector
                            :zones="environment.zones || []"
                            v-model="selectedZone"
                            :disabled="isApplyingMaterial"
                        />
                    </section>

                    <!-- Paso 2 -->
                    <section class="mt-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="h-7 w-7 rounded-full bg-[#1F1A17] text-white text-sm grid place-items-center font-semibold">
                                2
                            </span>

                            <h3 class="font-semibold text-[#1F1A17]">
                                Selecciona material
                            </h3>
                        </div>

                        <div
                            v-if="!selectedZone"
                            class="rounded-2xl border border-dashed border-black/10 p-4 text-sm text-[#6B5E55]"
                        >
                            Primero selecciona una zona editable.
                        </div>

                        <MaterialSelector
                            v-else
                            :categories="categories"
                            :active-material-id="currentZoneMaterial?.material?.id"
                            :disabled="isApplyingMaterial"
                            @select="applyMaterial"
                        />
                    </section>

                    <!-- Selección actual -->
                    <section
                        v-if="selectedZone"
                        class="mt-5 rounded-2xl bg-[#F8F5EF] border border-black/5 p-4"
                    >
                        <p class="text-xs uppercase tracking-[0.2em] text-[#9B6A3F] font-semibold">
                            Selección actual
                        </p>

                        <h3 class="mt-1 text-base font-semibold text-[#1F1A17]">
                            {{ selectedZone.name }}
                        </h3>

                        <div v-if="currentZoneMaterial" class="mt-3 flex gap-3 items-center">
                            <img
                                :src="currentZoneMaterial.material.thumbnail_url || currentZoneMaterial.material.texture_url"
                                class="h-12 w-12 rounded-xl object-cover bg-white"
                                :alt="currentZoneMaterial.material.name"
                            />

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#1F1A17] truncate">
                                    {{ currentZoneMaterial.material.name }}
                                </p>

                                <p class="text-xs text-[#6B5E55] truncate">
                                    {{ currentZoneMaterial.material.finish || currentZoneMaterial.material.base_color || 'Material aplicado' }}
                                </p>
                            </div>
                        </div>

                        <p v-else class="mt-3 text-sm text-[#6B5E55]">
                            No hay material aplicado en esta zona.
                        </p>
                    </section>

                    <div class="mt-5 space-y-3">
                        <button
                            type="button"
                            class="w-full h-11 rounded-2xl bg-[#1F1A17] text-white font-semibold hover:bg-black transition disabled:opacity-50"
                            :disabled="isApplyingMaterial"
                            @click="showLeadForm = true"
                        >
                            Solicitar cotización
                        </button>

                        <button
                            type="button"
                            class="w-full h-11 rounded-2xl border border-[#1F1A17]/20 text-[#1F1A17] font-semibold hover:bg-[#F8F5EF] transition"
                        >
                            Descargar inspiración
                        </button>
                    </div>

                    <!-- Debug compacto -->
                    <div class="mt-5 rounded-2xl bg-stone-50 border border-black/5 p-3 text-xs text-[#6B5E55]">
                        <p><strong>Zonas:</strong> {{ environment.zones?.length || 0 }}</p>
                        <p><strong>Categorías:</strong> {{ categories?.length || 0 }}</p>
                        <p><strong>Materiales activos:</strong> {{ totalMaterials }}</p>
                    </div>
                </div>
            </aside>
        </div>

        <LeadFormDrawer
            v-if="showLeadForm"
            :environment="environment"
            :selected-materials="selectedMaterials"
            @close="showLeadForm = false"
        />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

import DecoratorCanvas from '../../Components/Decorator/DecoratorCanvas.vue';
import ZoneSelector from '../../Components/Decorator/ZoneSelector.vue';
import MaterialSelector from '../../Components/Decorator/MaterialSelector.vue';
import LeadFormDrawer from '../../Components/Decorator/LeadFormDrawer.vue';

const props = defineProps({
    environment: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
});

const selectedZone = ref(props.environment.zones?.[0] || null);
const selectedMaterials = ref({});
const showLeadForm = ref(false);
const isApplyingMaterial = ref(false);

const totalMaterials = computed(() => {
    return (props.categories || []).reduce((total, category) => {
        return total + (category.materials?.length || 0);
    }, 0);
});

const hasAppliedMaterials = computed(() => {
    return Object.keys(selectedMaterials.value || {}).length > 0;
});

const appliedMaterialsList = computed(() => {
    return Object.values(selectedMaterials.value || {});
});

const currentZoneMaterial = computed(() => {
    if (!selectedZone.value) return null;

    return selectedMaterials.value[selectedZone.value.id] || null;
});


function applyMaterial(material) {
    if (isApplyingMaterial.value) return;

    if (!selectedZone.value) {
        alert('Primero selecciona una zona.');
        return;
    }

    if (!material?.texture_url) {
        alert('Este material no tiene textura cargada.');
        return;
    }

    selectedMaterials.value = {
        ...selectedMaterials.value,
        [selectedZone.value.id]: {
            zone: selectedZone.value,
            material,
            scale: Number(material.default_scale || selectedZone.value.default_texture_scale || 1),
            rotation: Number(material.default_rotation || selectedZone.value.default_texture_rotation || 0),
            opacity: Number(material.default_opacity || selectedZone.value.default_opacity || 1),
        },
    };
}

function clearMaterials() {
    selectedMaterials.value = {};
}
</script>
