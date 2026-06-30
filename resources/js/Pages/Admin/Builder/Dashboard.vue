<template>
    <BuilderLayout title="Dashboard">
        <div class="grid md:grid-cols-3 gap-4">
            <div
                v-for="card in cards"
                :key="card.label"
                class="rounded-3xl border border-white/10 bg-white/[0.06] p-5 flex items-start gap-4"
            >
                <div class="h-10 w-10 rounded-2xl bg-white/8 flex items-center justify-center shrink-0">
                    <component :is="card.icon" class="h-5 w-5 text-violet-300" />
                </div>
                <div>
                    <p class="text-sm text-white/50">{{ card.label }}</p>
                    <p class="mt-1 text-4xl font-bold">{{ card.value }}</p>
                </div>
            </div>
        </div>

        <!-- Preferencias del decorador -->
        <section class="mt-8">
            <h3 class="text-xs uppercase tracking-[0.25em] text-violet-300 mb-3">
                Preferencias del decorador
            </h3>

            <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5 flex items-center gap-4">
                <div class="h-10 w-10 rounded-2xl bg-white/8 flex items-center justify-center shrink-0">
                    <IconSparkles class="h-5 w-5 text-violet-300" />
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium">Botón "Render con IA"</p>
                    <p class="mt-0.5 text-xs text-white/50">
                        Muestra u oculta el botón de generar imagen con IA en la pantalla del usuario final.
                    </p>
                </div>

                <button
                    type="button"
                    role="switch"
                    :aria-checked="aiRenderEnabled"
                    :disabled="saving"
                    class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition disabled:opacity-50"
                    :class="aiRenderEnabled ? 'bg-violet-500' : 'bg-white/15'"
                    @click="toggleAiRender"
                >
                    <span
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"
                        :class="aiRenderEnabled ? 'translate-x-6' : 'translate-x-1'"
                    />
                </button>
            </div>
        </section>
    </BuilderLayout>
</template>

<script setup>
import { computed, h, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import BuilderLayout from '@/Components/AdminBuilder/BuilderLayout.vue';
import { useToast } from '@/composables/useToast.js';

const props = defineProps({
    stats: { type: Object, required: true },
    latestLeads: { type: Array, default: () => [] },
    settings: { type: Object, default: () => ({}) },
});

const { success, error } = useToast();

// ── Toggle: botón de Render con IA en la pantalla del usuario ─────────────────
const aiRenderEnabled = ref(props.settings.ai_render_enabled ?? true);
const saving = ref(false);

function toggleAiRender() {
    if (saving.value) return;

    const next = !aiRenderEnabled.value;
    saving.value = true;

    router.post('/admin/builder/settings', { ai_render_enabled: next }, {
        preserveScroll: true,
        onSuccess: () => {
            aiRenderEnabled.value = next;
            success(next ? 'Render con IA activado.' : 'Render con IA desactivado.');
        },
        onError: () => error('No se pudo guardar la preferencia.'),
        onFinish: () => { saving.value = false; },
    });
}

const IconSparkles = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z' }),
]) };

// Inline SVG icon components
const IconFolder = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z' }),
]) };

const IconGem = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z' }),
]) };

const IconPhoto = { render: () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z' }),
]) };

const cards = computed(() => [
    { label: 'Categorías',       value: props.stats.material_categories, icon: IconFolder },
    { label: 'Materiales activos', value: props.stats.active_materials,  icon: IconGem    },
    { label: 'Ambientes activos',  value: props.stats.active_environments, icon: IconPhoto },
]);
</script>
