<template>
    <section>
        <p class="text-[10px] uppercase tracking-[0.3em] text-white/35 font-semibold mb-3">
            ¿Dónde aplicar?
        </p>

        <!-- Grupos de zonas -->
        <template v-if="groups.length">
            <div class="space-y-2">
                <div
                    v-for="group in groups"
                    :key="'group-' + group.id"
                    class="border border-white/10 bg-white/[0.03]"
                >
                    <!-- Cabecera del grupo -->
                    <button
                        type="button"
                        class="w-full flex items-center justify-between px-3 py-2.5 transition hover:bg-white/[0.04]"
                        :disabled="disabled"
                        @click="toggleGroup(group.id)"
                    >
                        <div class="flex items-center gap-2.5">
                            <span
                                class="h-3 w-3 rounded-full shrink-0"
                                :style="{ backgroundColor: group.color || '#CC1A1A' }"
                            />
                            <span class="text-sm font-semibold text-white">{{ group.name }}</span>
                            <span class="text-[10px] text-white/30 uppercase tracking-[0.15em]">
                                {{ group.active_zones?.length || group.zones?.length || 0 }} zonas
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Indicador: material aplicado a alguna zona del grupo -->
                            <span
                                v-if="groupHasMaterial(group)"
                                class="h-1.5 w-1.5 rounded-full"
                                :style="{ backgroundColor: group.color || '#CC1A1A' }"
                            />
                            <svg
                                class="h-4 w-4 text-white/30 transition-transform"
                                :class="{ 'rotate-180': expandedGroups.includes(group.id) }"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    <!-- Zonas del grupo (expandible) -->
                    <div v-show="expandedGroups.includes(group.id)" class="border-t border-white/8 px-2 pb-2 pt-1.5 grid grid-cols-1 sm:grid-cols-2 gap-1">
                        <button
                            v-for="zone in (group.active_zones || group.zones || [])"
                            :key="zone.id"
                            type="button"
                            class="border px-3 py-2.5 text-left transition disabled:opacity-40"
                            :class="isSelected(zone)
                                ? 'border-[#CC1A1A] bg-[#CC1A1A]/10 text-white'
                                : 'border-white/8 bg-white/[0.02] text-white/60 hover:border-white/20 hover:text-white'"
                            :style="isSelected(zone) ? { borderColor: group.color, backgroundColor: group.color + '18' } : {}"
                            :disabled="disabled"
                            @click="$emit('update:modelValue', zone)"
                        >
                            <p class="text-xs font-semibold truncate">{{ zone.name }}</p>
                            <p
                                class="text-[10px] uppercase tracking-[0.15em] mt-0.5 truncate"
                                :style="isSelected(zone) ? { color: group.color } : {}"
                                :class="!isSelected(zone) ? 'text-white/25' : ''"
                            >
                                {{ zone.zone_type || 'Zona' }}
                            </p>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Zonas sin grupo -->
            <template v-if="ungroupedZones.length">
                <p class="text-[10px] uppercase tracking-[0.25em] text-white/25 font-semibold mt-4 mb-2">
                    Otras zonas
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                    <button
                        v-for="zone in ungroupedZones"
                        :key="zone.id"
                        type="button"
                        class="border px-4 py-3 text-left transition disabled:opacity-40"
                        :class="isSelected(zone)
                            ? 'border-[#CC1A1A] bg-[#CC1A1A]/10 text-white'
                            : 'border-white/10 bg-white/[0.03] text-white/70 hover:border-white/25 hover:text-white'"
                        :disabled="disabled"
                        @click="$emit('update:modelValue', zone)"
                    >
                        <p class="text-sm font-semibold truncate">{{ zone.name }}</p>
                        <p
                            class="text-[10px] uppercase tracking-[0.2em] mt-1 truncate"
                            :class="isSelected(zone) ? 'text-[#CC1A1A]/70' : 'text-white/30'"
                        >
                            {{ zone.zone_type || 'Zona editable' }}
                        </p>
                    </button>
                </div>
            </template>
        </template>

        <!-- Sin grupos: lista plana original -->
        <template v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-1.5">
                <button
                    v-for="zone in zones"
                    :key="zone.id"
                    type="button"
                    class="border px-4 py-3 text-left transition disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="isSelected(zone)
                        ? 'border-[#CC1A1A] bg-[#CC1A1A]/10 text-white'
                        : 'border-white/10 bg-white/[0.03] text-white/70 hover:border-white/25 hover:text-white'"
                    :disabled="disabled"
                    @click="$emit('update:modelValue', zone)"
                >
                    <p class="text-sm font-semibold truncate">{{ zone.name }}</p>
                    <p
                        class="text-[10px] uppercase tracking-[0.2em] mt-1 truncate"
                        :class="isSelected(zone) ? 'text-[#CC1A1A]/70' : 'text-white/30'"
                    >
                        {{ zone.zone_type || 'Zona editable' }}
                    </p>
                </button>

                <div
                    v-if="!zones.length"
                    class="border border-dashed border-white/10 p-4 text-xs text-white/30"
                >
                    Sin zonas editables configuradas.
                </div>
            </div>
        </template>
    </section>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    zones:             { type: Array,  default: () => [] },
    groups:            { type: Array,  default: () => [] },
    selectedMaterials: { type: Object, default: () => ({}) },
    modelValue:        { type: Object, default: null },
    disabled:          { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);

// IDs de grupos que están expandidos
const expandedGroups = ref([]);

// Al montar, expande el primer grupo con zonas
if (props.groups.length) {
    expandedGroups.value = [props.groups[0].id];
}

function toggleGroup(groupId) {
    const idx = expandedGroups.value.indexOf(groupId);
    if (idx === -1) {
        expandedGroups.value.push(groupId);
    } else {
        expandedGroups.value.splice(idx, 1);
    }
}

function isSelected(zone) {
    return props.modelValue?.id === zone.id;
}

// Zonas que no pertenecen a ningún grupo
const ungroupedZones = computed(() => {
    const groupedZoneIds = new Set(
        props.groups.flatMap((g) => (g.active_zones || g.zones || []).map((z) => z.id))
    );
    return props.zones.filter((z) => !groupedZoneIds.has(z.id));
});

function groupHasMaterial(group) {
    const zones = group.active_zones || group.zones || [];
    return zones.some((z) => props.selectedMaterials?.[z.id]);
}
</script>
