<template>
    <section>
        <p class="text-[10px] uppercase tracking-[0.3em] text-white/35 font-semibold mb-3">
            ¿Dónde aplicar?
        </p>

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
                <p class="text-sm font-semibold truncate">
                    {{ zone.name }}
                </p>

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
    </section>
</template>

<script setup>
const props = defineProps({
    zones: { type: Array, default: () => [] },
    modelValue: { type: Object, default: null },
    disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);

function isSelected(zone) {
    return props.modelValue?.id === zone.id;
}
</script>
