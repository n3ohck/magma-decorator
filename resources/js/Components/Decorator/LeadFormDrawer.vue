<template>
    <div class="fixed inset-0 z-50">
        <div
            class="absolute inset-0 bg-black/40 backdrop-blur-sm"
            @click="$emit('close')"
        ></div>

        <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl p-6 overflow-y-auto">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-[#9B6A3F] font-semibold">
                        Cotización
                    </p>

                    <h2 class="text-2xl font-semibold text-[#1F1A17]">
                        Solicitar asesoría
                    </h2>

                    <p class="text-sm text-[#6B5E55] mt-2">
                        Envíanos tus datos y un asesor de Magma te contactará con base en este diseño.
                    </p>
                </div>

                <button
                    type="button"
                    class="h-10 w-10 rounded-full bg-stone-100 text-[#1F1A17]"
                    @click="$emit('close')"
                >
                    ✕
                </button>
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="text-sm font-semibold text-[#1F1A17]">
                        Nombre *
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 w-full h-12 rounded-2xl border border-black/10 px-4 outline-none focus:border-[#9B6A3F]"
                        required
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-[#1F1A17]">
                        Teléfono
                    </label>
                    <input
                        v-model="form.phone"
                        type="text"
                        class="mt-1 w-full h-12 rounded-2xl border border-black/10 px-4 outline-none focus:border-[#9B6A3F]"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-[#1F1A17]">
                        Correo
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-1 w-full h-12 rounded-2xl border border-black/10 px-4 outline-none focus:border-[#9B6A3F]"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-[#1F1A17]">
                        Ciudad
                    </label>
                    <input
                        v-model="form.city"
                        type="text"
                        class="mt-1 w-full h-12 rounded-2xl border border-black/10 px-4 outline-none focus:border-[#9B6A3F]"
                    />
                </div>

                <div>
                    <label class="text-sm font-semibold text-[#1F1A17]">
                        Tipo de proyecto
                    </label>
                    <select
                        v-model="form.project_type"
                        class="mt-1 w-full h-12 rounded-2xl border border-black/10 px-4 outline-none focus:border-[#9B6A3F] bg-white"
                    >
                        <option value="">Seleccionar</option>
                        <option value="kitchen">Cocina</option>
                        <option value="bathroom">Baño</option>
                        <option value="residential">Residencial</option>
                        <option value="commercial">Comercial</option>
                        <option value="exterior">Exterior</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-[#1F1A17]">
                        Mensaje
                    </label>
                    <textarea
                        v-model="form.message"
                        rows="4"
                        class="mt-1 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-[#9B6A3F]"
                        placeholder="Cuéntanos un poco sobre tu proyecto..."
                    ></textarea>
                </div>

                <div class="rounded-2xl bg-stone-50 border border-black/5 p-4">
                    <p class="text-sm font-semibold text-[#1F1A17]">
                        Ambiente seleccionado
                    </p>
                    <p class="text-sm text-[#6B5E55]">
                        {{ environment.name }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full h-12 rounded-2xl bg-[#1F1A17] text-white font-semibold hover:bg-black transition disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Enviando...' : 'Enviar solicitud' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    environment: {
        type: Object,
        required: true,
    },
    selectedMaterials: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    environment_id: props.environment.id,
    name: '',
    email: '',
    phone: '',
    city: '',
    project_type: '',
    preferred_contact_method: 'whatsapp',
    message: '',
    snapshot: {
        environment: props.environment,
        selected_materials: props.selectedMaterials,
    },
    final_image: null,
});

function submit() {
    form.snapshot = {
        environment: props.environment,
        selected_materials: props.selectedMaterials,
    };

    form.post('/decorador/leads', {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
}
</script>
