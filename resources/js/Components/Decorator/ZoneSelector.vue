<template>
    <section>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-[#9B6A3F] font-semibold">
                    Zonas
                </p>

                <h2 class="text-base font-semibold text-[#1F1A17]">
                    ¿Dónde quieres aplicar material?
                </h2>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-2 mt-3">
            <button
                v-for="zone in zones"
                :key="zone.id"
                type="button"
                class="rounded-2xl border px-4 py-3 text-left transition disabled:opacity-60 disabled:cursor-not-allowed"
                :class="isSelected(zone)
                    ? 'border-[#1F1A17] bg-[#1F1A17] text-white shadow-md'
                    : 'border-black/10 bg-white text-[#1F1A17] hover:border-[#9B6A3F]'"
                :disabled="disabled"
                @click="$emit('update:modelValue', zone)"
            >
                <p class="text-sm font-semibold truncate">
                    {{ zone.name }}
                </p>

                <p
                    class="text-xs mt-1 truncate"
                    :class="isSelected(zone) ? 'text-white/70' : 'text-[#6B5E55]'"
                >
                    {{ zone.zone_type || 'Zona editable' }}
                </p>
            </button>

            <div
                v-if="!zones.length"
                class="rounded-2xl border border-dashed border-black/10 p-4 text-sm text-[#6B5E55]"
            >
                Este ambiente todavía no tiene zonas editables.
            </div>
        </div>
    </section>
</template>

<script setup>
const props = defineProps({
    zones: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: Object,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);

function isSelected(zone) {
    return props.modelValue?.id === zone.id;
}
</script>
