<template>
    <BuilderLayout title="Dashboard">
        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div
                v-for="card in cards"
                :key="card.label"
                class="rounded-3xl border border-white/10 bg-white/[0.06] p-5"
            >
                <p class="text-sm text-white/50">
                    {{ card.label }}
                </p>
                <p class="mt-2 text-4xl font-bold">
                    {{ card.value }}
                </p>
            </div>
        </div>

        <div class="mt-8 rounded-3xl border border-white/10 bg-white/[0.06] overflow-hidden">
            <div class="p-5 border-b border-white/10">
                <h3 class="text-lg font-bold">
                    Últimas solicitudes
                </h3>
            </div>

            <div class="divide-y divide-white/10">
                <div
                    v-for="lead in latestLeads"
                    :key="lead.id"
                    class="p-5 flex items-center justify-between gap-4"
                >
                    <div>
                        <p class="font-semibold">
                            {{ lead.name }}
                        </p>
                        <p class="text-sm text-white/50">
                            {{ lead.phone || lead.email || 'Sin contacto' }}
                        </p>
                    </div>

                    <span class="rounded-full bg-violet-500/20 text-violet-200 px-3 py-1 text-xs">
                        {{ lead.status }}
                    </span>
                </div>

                <div v-if="!latestLeads.length" class="p-6 text-white/50">
                    Todavía no hay solicitudes.
                </div>
            </div>
        </div>
    </BuilderLayout>
</template>

<script setup>
import { computed } from 'vue';
import BuilderLayout from '@/Components/AdminBuilder/BuilderLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    latestLeads: {
        type: Array,
        default: () => [],
    },
});

const cards = computed(() => [
    { label: 'Categorías', value: props.stats.material_categories },
    { label: 'Materiales activos', value: props.stats.active_materials },
    { label: 'Ambientes activos', value: props.stats.active_environments },
    { label: 'Leads nuevos', value: props.stats.new_leads },
]);
</script>
