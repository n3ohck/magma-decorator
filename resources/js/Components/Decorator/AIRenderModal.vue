<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')" />

        <div class="relative w-full max-w-2xl rounded-3xl bg-[#1F1A17] border border-white/10 shadow-2xl overflow-hidden">

            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/8">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-[#9B6A3F] font-semibold">
                        Inteligencia Artificial
                    </p>
                    <h2 class="mt-0.5 text-xl font-semibold text-white">
                        Render fotorrealista
                    </h2>
                </div>

                <button
                    class="h-9 w-9 rounded-full bg-white/10 text-white/60 hover:bg-white/20 transition text-xl leading-none"
                    @click="$emit('close')"
                >
                    ×
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">

                <!-- Idle -->
                <div v-if="state === 'idle'" class="space-y-4">
                    <p class="text-sm text-white/60">
                        La IA tomará tu diseño actual y lo procesará para producir
                        una imagen fotorrealista de alta calidad con los materiales aplicados.
                    </p>

                    <div class="rounded-2xl bg-white/5 border border-white/8 p-4 text-sm text-white/50 space-y-1">
                        <p>· El proceso toma entre 15 y 30 segundos.</p>
                        <p>· El resultado puede descargarse directamente.</p>
                        <p>· Cuantos más materiales estén aplicados, mejor será el contexto.</p>
                    </div>

                    <p v-if="!hasMaterials" class="text-sm text-amber-300/80">
                        Aún no has aplicado ningún material. Aplica al menos uno para
                        obtener un mejor resultado.
                    </p>

                    <button
                        class="w-full h-12 rounded-2xl bg-[#9B6A3F] text-white font-semibold hover:bg-[#b07840] transition"
                        @click="startRender"
                    >
                        Generar render con IA
                    </button>
                </div>

                <!-- Loading -->
                <div v-else-if="state === 'loading'" class="py-10 flex flex-col items-center gap-5">
                    <div class="relative h-16 w-16">
                        <span class="absolute inset-0 rounded-full border-4 border-white/10" />
                        <span class="absolute inset-0 rounded-full border-4 border-t-[#9B6A3F] animate-spin" />
                    </div>

                    <div class="text-center">
                        <p class="text-base font-semibold text-white">Procesando con IA…</p>
                        <p class="mt-1 text-sm text-white/50">{{ loadingMessage }}</p>
                    </div>
                </div>

                <!-- Result -->
                <div v-else-if="state === 'done'" class="space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-white/10">
                        <img
                            :src="outputUrl"
                            alt="Render generado por IA"
                            class="w-full object-contain max-h-[60vh]"
                        />
                    </div>

                    <div class="flex gap-3">
                        <a
                            :href="outputUrl"
                            target="_blank"
                            download="render-magma.png"
                            class="flex-1 h-11 rounded-2xl bg-[#9B6A3F] text-white font-semibold hover:bg-[#b07840] transition flex items-center justify-center gap-2"
                        >
                            Descargar imagen
                        </a>

                        <button
                            class="h-11 px-5 rounded-2xl border border-white/15 text-white/70 font-semibold hover:bg-white/5 transition"
                            @click="reset"
                        >
                            Generar otro
                        </button>
                    </div>
                </div>

                <!-- Error -->
                <div v-else-if="state === 'error'" class="space-y-4">
                    <div class="rounded-2xl bg-red-500/10 border border-red-500/20 p-4 text-sm text-red-300">
                        <p class="font-semibold">No se pudo generar el render</p>
                        <p class="mt-1 text-red-300/70">{{ errorMessage }}</p>
                    </div>

                    <button
                        class="w-full h-11 rounded-2xl border border-white/15 text-white/70 font-semibold hover:bg-white/5 transition"
                        @click="reset"
                    >
                        Intentar de nuevo
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    captureCanvas: {
        type: Function,
        required: true,
    },
    materials: {
        type: Array,
        default: () => [],
    },
});

defineEmits(['close']);

const state = ref('idle');      // idle | loading | done | error
const outputUrl = ref(null);
const errorMessage = ref('');
const loadingMessage = ref('Iniciando predicción…');

const hasMaterials = computed(() => props.materials.length > 0);

let pollTimer = null;

function reset() {
    state.value = 'idle';
    outputUrl.value = null;
    errorMessage.value = '';
    if (pollTimer) clearTimeout(pollTimer);
}

async function startRender() {
    const imageData = props.captureCanvas();

    if (!imageData) {
        errorMessage.value = 'No se pudo capturar el canvas. Intenta de nuevo.';
        state.value = 'error';
        return;
    }

    state.value = 'loading';
    loadingMessage.value = 'Subiendo diseño al servidor…';

    try {
        const { data } = await axios.post('/decorador/ai-render', {
            image_data: imageData,
            materials: props.materials.map((m) => ({ name: m.material?.name })),
        });

        loadingMessage.value = 'Modelo IA procesando tu diseño…';
        pollStatus(data.prediction_id);

    } catch (err) {
        state.value = 'error';
        errorMessage.value = err?.response?.data?.error ?? 'Error al iniciar el render.';
    }
}

function pollStatus(predictionId) {
    pollTimer = setTimeout(async () => {
        try {
            const { data } = await axios.get(`/decorador/ai-render/${predictionId}/status`);

            if (data.status === 'succeeded') {
                outputUrl.value = data.output_url;
                state.value = 'done';

            } else if (data.status === 'failed' || data.status === 'canceled') {
                state.value = 'error';
                errorMessage.value = data.error ?? 'El modelo falló al procesar la imagen.';

            } else {
                // Still processing — keep polling
                const messages = [
                    'Aplicando materiales fotorrealistas…',
                    'Ajustando iluminación y sombras…',
                    'Refinando detalles de superficie…',
                    'Casi listo…',
                ];
                loadingMessage.value = messages[Math.floor(Math.random() * messages.length)];
                pollStatus(predictionId);
            }

        } catch (err) {
            state.value = 'error';
            errorMessage.value = 'Error al consultar el estado del render.';
        }
    }, 3000);
}
</script>
