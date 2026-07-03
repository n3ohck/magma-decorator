<template>
    <div class="h-screen overflow-hidden bg-[#0D0D0D]">
        <div class="grid h-full overflow-hidden lg:grid-cols-[minmax(0,1fr)_400px] xl:grid-cols-[minmax(0,1fr)_440px]">

            <!-- Canvas area -->
            <main class="min-w-0 h-full overflow-hidden flex flex-col bg-[#0D0D0D]">

                <!-- Top bar -->
                <header class="shrink-0 border-b border-white/8 px-5 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <!-- Logo -->
                        <a href="/" class="shrink-0">
                            <img
                                :src="'/images/magma-logo.png'"
                                alt="Magma Superficies"
                                class="h-8 w-auto object-contain"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block'"
                            />
                            <span
                                class="hidden text-xs font-bold uppercase tracking-[0.2em] text-white"
                                style="display:none"
                            >
                                Magma
                            </span>
                        </a>

                        <div class="h-4 w-px bg-white/10 shrink-0" />

                        <a
                            href="/decorador"
                            class="text-[10px] uppercase tracking-[0.25em] text-white/35 hover:text-white/70 transition shrink-0"
                        >
                            ← Ambientes
                        </a>

                        <div class="h-4 w-px bg-white/10 shrink-0" />

                        <h1 class="text-sm font-bold text-white uppercase tracking-[0.15em] truncate">
                            {{ environment.name }}
                        </h1>
                    </div>

                    <!-- Active zone pill -->
                    <div
                        v-if="selectedZone"
                        class="hidden md:flex items-center gap-2 border border-white/12 px-3 py-1.5 shrink-0"
                    >
                        <span class="h-1.5 w-1.5 bg-[#CC1A1A]" />
                        <span class="text-[10px] uppercase tracking-[0.2em] text-white/60">
                            {{ selectedZone.name }}
                        </span>
                    </div>
                </header>

                <!-- Canvas -->
                <section class="min-h-0 flex-1 overflow-hidden p-3">
                    <DecoratorCanvas
                        ref="canvasRef"
                        :environment="environment"
                        :selected-zone="selectedZone"
                        :selected-materials="selectedMaterials"
                        @applying-change="isApplyingMaterial = $event"
                        @select-group="selectGroup"
                    />
                </section>

                <!-- Applied materials strip -->
                <footer class="shrink-0 border-t border-white/8 px-4 py-3 flex items-center gap-4">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] uppercase tracking-[0.3em] text-white/30">
                            {{ hasAppliedMaterials ? `${appliedMaterialsList.length} material(es) aplicado(s)` : 'Sin materiales aplicados' }}
                        </p>

                        <div v-if="hasAppliedMaterials" class="mt-2 flex gap-2 overflow-x-auto pb-0.5">
                            <div
                                v-for="item in appliedMaterialsList"
                                :key="item.zone.id"
                                class="shrink-0 border border-white/10 bg-white/[0.04] px-2.5 py-1.5 flex items-center gap-2 max-w-[200px]"
                            >
                                <img
                                    v-if="item.material.thumbnail_url || item.material.texture_url"
                                    :src="item.material.thumbnail_url || item.material.texture_url"
                                    class="h-6 w-6 object-cover bg-black/40"
                                    :alt="item.material.name"
                                />
                                <div class="min-w-0">
                                    <p class="text-[10px] text-white/60 truncate">{{ item.zone.name }}</p>
                                    <p class="text-[10px] font-semibold text-white truncate">{{ item.material.name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        v-if="hasAppliedMaterials"
                        type="button"
                        class="shrink-0 text-[10px] uppercase tracking-[0.2em] text-white/30 hover:text-white/60 transition disabled:opacity-30"
                        :disabled="isApplyingMaterial"
                        @click="clearMaterials"
                    >
                        Limpiar
                    </button>
                </footer>
            </main>

            <!-- Sidebar -->
            <aside class="h-full min-h-0 overflow-y-auto bg-[#111111] border-l border-white/8 flex flex-col">

                <!-- Sidebar header -->
                <div class="sticky top-0 bg-[#111111] z-10 border-b border-white/8 px-5 py-5">
                    <p class="text-[10px] uppercase tracking-[0.35em] text-[#CC1A1A] font-semibold">
                        Configurador
                    </p>
                    <h2 class="mt-1 text-xl font-bold text-white uppercase tracking-wide">
                        Personaliza tu espacio
                    </h2>
                </div>

                <div class="p-5 flex-1 flex flex-col gap-6">

                    <!-- Step 1 -->
                    <section>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="h-6 w-6 bg-white/10 border border-white/15 text-white text-xs grid place-items-center font-bold shrink-0">
                                1
                            </span>
                            <h3 class="text-xs uppercase tracking-[0.25em] text-white/70 font-semibold">
                                Zona
                            </h3>
                        </div>

                        <ZoneSelector
                            :zones="environment.zones || []"
                            v-model="selectedZone"
                            :active-group-zone-ids="activeGroupZoneIds"
                            :disabled="isApplyingMaterial"
                        />
                    </section>

                    <!-- Step 2 -->
                    <section>
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="h-6 w-6 border text-xs grid place-items-center font-bold shrink-0 transition"
                                :class="selectedZone
                                    ? 'bg-white/10 border-white/15 text-white'
                                    : 'bg-transparent border-white/8 text-white/20'"
                            >
                                2
                            </span>
                            <h3
                                class="text-xs uppercase tracking-[0.25em] font-semibold transition"
                                :class="selectedZone ? 'text-white/70' : 'text-white/20'"
                            >
                                Material
                            </h3>
                        </div>

                        <div
                            v-if="!selectedZone"
                            class="border border-dashed border-white/8 p-4 text-xs text-white/25"
                        >
                            Selecciona primero una zona.
                        </div>

                        <MaterialSelector
                            v-else
                            :categories="categories"
                            :active-material-id="currentZoneMaterial?.material?.id"
                            :disabled="isApplyingMaterial"
                            @select="applyMaterial"
                        />
                    </section>

                    <!-- Current selection + adjustments -->
                    <section v-if="selectedZone" class="border border-white/8 bg-white/[0.03] p-4">
                        <p class="text-[10px] uppercase tracking-[0.3em] text-white/30 mb-3">
                            {{ selectedZone.name }}
                        </p>

                        <div v-if="currentZoneMaterial" class="flex items-center gap-3">
                            <img
                                :src="currentZoneMaterial.material.thumbnail_url || currentZoneMaterial.material.texture_url"
                                class="h-10 w-10 object-cover bg-black/40 shrink-0"
                                :alt="currentZoneMaterial.material.name"
                            />
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-white truncate">
                                    {{ currentZoneMaterial.material.name }}
                                </p>
                                <p class="text-[10px] text-white/40 truncate">
                                    {{ currentZoneMaterial.material.finish || currentZoneMaterial.material.base_color || 'Aplicado' }}
                                </p>
                            </div>
                        </div>

                        <p v-else class="text-xs text-white/30">
                            Sin material en esta zona.
                        </p>

                        <!-- Sliders — key por zona+material fuerza re-mount al cambiar selección -->
                        <div
                            v-if="currentZoneMaterial"
                            :key="`${selectedZone?.id}-${currentZoneMaterial?.material?.id}`"
                            class="mt-4 space-y-4 pt-4 border-t border-white/8"
                        >
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[10px] uppercase tracking-[0.2em] text-white/40">Escala</span>
                                    <span class="text-xs font-semibold text-white tabular-nums">
                                        {{ Number(currentZoneMaterial.scale).toFixed(1) }}×
                                    </span>
                                </div>
                                <input
                                    type="range" min="0.2" max="6" step="0.1"
                                    :value="currentZoneMaterial.scale"
                                    :disabled="isApplyingMaterial || currentZoneMaterial.bookMatch"
                                    class="w-full h-px accent-[#CC1A1A] disabled:opacity-30"
                                    @input="updateAdjustment('scale', Number($event.target.value))"
                                />
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[10px] uppercase tracking-[0.2em] text-white/40">Rotación</span>
                                    <span class="text-xs font-semibold text-white tabular-nums">
                                        {{ Math.round(currentZoneMaterial.rotation) }}°
                                    </span>
                                </div>
                                <input
                                    type="range" min="0" max="360" step="1"
                                    :value="currentZoneMaterial.rotation"
                                    :disabled="isApplyingMaterial || currentZoneMaterial.bookMatch"
                                    class="w-full h-px accent-[#CC1A1A] disabled:opacity-30"
                                    @input="updateAdjustment('rotation', Number($event.target.value))"
                                />
                            </div>

                            <!-- Book Match: espejo 4 vías (H+V) sobre esta capa -->
                            <div class="flex items-center justify-between pt-1">
                                <div class="min-w-0 pr-3">
                                    <span class="text-[10px] uppercase tracking-[0.2em] text-white/40">Book Match</span>
                                    <p class="text-[10px] text-white/25 mt-0.5 leading-tight">
                                        Espejo simétrico auto-ajustado al muro. Aplica a todo el grupo.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="!!currentZoneMaterial.bookMatch"
                                    :disabled="isApplyingMaterial"
                                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition disabled:opacity-30"
                                    :class="currentZoneMaterial.bookMatch ? 'bg-[#CC1A1A]' : 'bg-white/15'"
                                    @click="toggleBookMatch"
                                >
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                                        :class="currentZoneMaterial.bookMatch ? 'translate-x-6' : 'translate-x-1'"
                                    />
                                </button>
                            </div>

                            <!-- Selector de modo, solo con Book Match activo -->
                            <div v-if="currentZoneMaterial.bookMatch" class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    class="flex-1 h-8 border text-[10px] font-semibold uppercase tracking-[0.15em] transition disabled:opacity-30"
                                    :class="(currentZoneMaterial.bookMatchMode || 'two') === 'two'
                                        ? 'border-[#CC1A1A] bg-[#CC1A1A]/10 text-white'
                                        : 'border-white/12 bg-white/[0.03] text-white/50 hover:text-white'"
                                    :disabled="isApplyingMaterial"
                                    @click="setBookMatchMode('two')"
                                >
                                    2 vías · Vertical
                                </button>
                                <button
                                    type="button"
                                    class="flex-1 h-8 border text-[10px] font-semibold uppercase tracking-[0.15em] transition disabled:opacity-30"
                                    :class="(currentZoneMaterial.bookMatchMode || 'two') === 'four'
                                        ? 'border-[#CC1A1A] bg-[#CC1A1A]/10 text-white'
                                        : 'border-white/12 bg-white/[0.03] text-white/50 hover:text-white'"
                                    :disabled="isApplyingMaterial"
                                    @click="setBookMatchMode('four')"
                                >
                                    4 vías · Diamante
                                </button>
                            </div>

                            <button
                                type="button"
                                class="text-[10px] uppercase tracking-[0.2em] text-white/30 hover:text-white/60 transition disabled:opacity-30"
                                :disabled="isApplyingMaterial"
                                @click="resetAdjustments"
                            >
                                Restablecer
                            </button>
                        </div>
                    </section>

                    <!-- CTAs -->
                    <div class="mt-auto space-y-2">
                        <!-- Solicitar cotización oculto temporalmente
                        <button
                            type="button"
                            class="w-full h-12 bg-[#CC1A1A] text-white text-xs font-bold uppercase tracking-[0.25em] hover:bg-[#E01F1F] transition disabled:opacity-40"
                            :disabled="isApplyingMaterial"
                            @click="showLeadForm = true"
                        >
                            Solicitar cotización
                        </button>
                        -->

                        <button
                            v-if="aiRenderEnabled"
                            type="button"
                            class="w-full h-11 border border-white/15 text-white/60 text-xs font-bold uppercase tracking-[0.2em] hover:border-white/35 hover:text-white transition flex items-center justify-center gap-2 disabled:opacity-30"
                            :disabled="isApplyingMaterial"
                            @click="showAIRender = true"
                        >
                            <span class="text-[#CC1A1A]">✦</span>
                            Render con IA
                        </button>
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

        <AIRenderModal
            v-if="showAIRender"
            :capture-canvas="() => canvasRef?.captureImage()"
            :materials="appliedMaterialsList"
            @close="showAIRender = false"
        />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

import DecoratorCanvas from '../../Components/Decorator/DecoratorCanvas.vue';
import ZoneSelector from '../../Components/Decorator/ZoneSelector.vue';
import MaterialSelector from '../../Components/Decorator/MaterialSelector.vue';
import LeadFormDrawer from '../../Components/Decorator/LeadFormDrawer.vue';
import AIRenderModal from '../../Components/Decorator/AIRenderModal.vue';

const props = defineProps({
    environment: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    aiRenderEnabled: {
        type: Boolean,
        default: true,
    },
});

const canvasRef = ref(null);
const selectedZone = ref(props.environment.zones?.[0] || null);
const selectedMaterials = ref({});
const showLeadForm = ref(false);
const showAIRender = ref(false);
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


// Grupo al que pertenece la zona seleccionada (para resaltar sus hermanas).
const activeGroup = computed(() => {
    const zid = selectedZone.value?.id;
    if (zid == null) return null;
    return (props.environment.active_zone_groups || []).find(
        (g) => (g.active_zones || g.zones || []).some((z) => z.id === zid),
    ) || null;
});

const activeGroupZoneIds = computed(() => {
    const group = activeGroup.value;
    if (!group) return [];
    return (group.active_zones || group.zones || []).map((z) => z.id);
});

// Zonas que deben recibir un ajuste (escala/rotación): todas las del grupo activo
// que tengan material aplicado; si la zona no pertenece a un grupo, sólo ella.
const adjustmentTargetZoneIds = computed(() => {
    const zid = selectedZone.value?.id;
    if (zid == null) return [];
    const ids = activeGroupZoneIds.value.length ? activeGroupZoneIds.value : [zid];
    return ids.filter((id) => selectedMaterials.value[id]);
});

// Clic en el punto del canvas → selecciona el grupo (su primera zona).
// El material se aplica a todo el grupo, así que basta con activar una zona.
function selectGroup(group) {
    if (isApplyingMaterial.value) return;
    const zones = group.active_zones || group.zones || [];
    if (!zones.length) return;
    selectedZone.value = zones[0];
}

// Toda textura recién aplicada arranca con esta escala.
const INITIAL_TEXTURE_SCALE = 0.3;

function buildZoneEntry(zone, material) {
    return {
        zone,
        material,
        scale:     INITIAL_TEXTURE_SCALE,
        rotation:  Number(material.default_rotation || zone.default_texture_rotation || 0),
        opacity:   Number(material.default_opacity  || zone.default_opacity          || 1),
        bookMatch: Boolean(zone.default_book_match),
        bookMatchMode: 'two',   // '' 'two' = spine vertical · 'four' = mariposa/diamante
    };
}

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

    // Si la zona pertenece a un grupo, aplica a todas las zonas del grupo
    const group = (props.environment.active_zone_groups || []).find(
        (g) => (g.active_zones || g.zones || []).some((z) => z.id === selectedZone.value.id)
    );

    const updates = {};

    if (group) {
        const groupZones = group.active_zones || group.zones || [];
        for (const zone of groupZones) {
            updates[zone.id] = buildZoneEntry(zone, material);
        }
    } else {
        updates[selectedZone.value.id] = buildZoneEntry(selectedZone.value, material);
    }

    selectedMaterials.value = { ...selectedMaterials.value, ...updates };
}

function clearMaterials() {
    selectedMaterials.value = {};
}

function updateAdjustment(prop, value) {
    if (!currentZoneMaterial.value) return;
    const ids = adjustmentTargetZoneIds.value;
    if (!ids.length) return;

    const next = { ...selectedMaterials.value };
    for (const id of ids) {
        if (!next[id]) continue;
        next[id] = { ...next[id], [prop]: value };
    }
    selectedMaterials.value = next;
}

function resetAdjustments() {
    if (!currentZoneMaterial.value) return;
    const ids = adjustmentTargetZoneIds.value;
    if (!ids.length) return;

    const next = { ...selectedMaterials.value };
    for (const id of ids) {
        const entry = next[id];
        if (!entry) continue;
        const { material, zone } = entry;
        next[id] = {
            ...entry,
            scale: INITIAL_TEXTURE_SCALE,
            rotation: Number(material.default_rotation || zone.default_texture_rotation || 0),
            bookMatch: false,
            bookMatchMode: 'two',
        };
    }
    selectedMaterials.value = next;
}

// Book Match (auto): si la zona pertenece a un grupo, se aplica a TODO el muro (grupo)
// con un eje de espejo común; si es zona suelta, sólo a esa capa.
function toggleBookMatch() {
    if (!currentZoneMaterial.value) return;
    const ids = adjustmentTargetZoneIds.value;
    if (!ids.length) return;

    const nextVal = !currentZoneMaterial.value.bookMatch;
    const next = { ...selectedMaterials.value };
    for (const id of ids) {
        if (next[id]) next[id] = { ...next[id], bookMatch: nextVal };
    }
    selectedMaterials.value = next;
}

// Modo de espejo del book match ('two' = spine vertical · 'four' = diamante).
// Se aplica a todo el grupo, igual que el toggle.
function setBookMatchMode(mode) {
    if (!currentZoneMaterial.value) return;
    const ids = adjustmentTargetZoneIds.value;
    if (!ids.length) return;

    const next = { ...selectedMaterials.value };
    for (const id of ids) {
        if (next[id]) next[id] = { ...next[id], bookMatchMode: mode };
    }
    selectedMaterials.value = next;
}
</script>
