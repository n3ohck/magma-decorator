<template>
    <div class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')" />

        <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-[#0D0D0D] border-l border-white/10 shadow-2xl overflow-y-auto">

            <!-- Header -->
            <div class="sticky top-0 bg-[#0D0D0D] border-b border-white/8 px-6 py-5 flex items-start justify-between gap-4 z-10">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.35em] text-[#CC1A1A] font-semibold">
                        Cotización
                    </p>

                    <h2 class="mt-1 text-2xl font-bold text-white uppercase tracking-wide">
                        Solicitar asesoría
                    </h2>

                    <p class="text-xs text-white/40 mt-1.5 leading-relaxed">
                        Un especialista de Magma te contactará con base en este diseño.
                    </p>
                </div>

                <button
                    type="button"
                    class="shrink-0 h-9 w-9 border border-white/15 text-white/50 hover:bg-white/8 hover:text-white transition flex items-center justify-center"
                    @click="$emit('close')"
                >
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form class="px-6 py-6 space-y-4" @submit.prevent="submit">

                <!-- Design summary -->
                <div
                    v-if="Object.keys(selectedMaterials).length"
                    class="border border-white/8 bg-white/[0.03] p-4"
                >
                    <p class="text-[10px] uppercase tracking-[0.3em] text-white/35 mb-3">
                        Diseño seleccionado
                    </p>

                    <div class="space-y-2">
                        <div
                            v-for="item in materialsList"
                            :key="item.zone.id"
                            class="flex items-center gap-3"
                        >
                            <img
                                v-if="item.material.thumbnail_url || item.material.texture_url"
                                :src="item.material.thumbnail_url || item.material.texture_url"
                                class="h-8 w-8 object-cover bg-black/40"
                                :alt="item.material.name"
                            />
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-white truncate">{{ item.zone.name }}</p>
                                <p class="text-[10px] text-white/40 truncate">{{ item.material.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.25em] text-white/50 mb-2">Nombre *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full h-11 border border-white/12 bg-white/[0.05] px-4 text-sm text-white placeholder:text-white/20 outline-none focus:border-white/35 transition"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.25em] text-white/50 mb-2">Teléfono</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full h-11 border border-white/12 bg-white/[0.05] px-4 text-sm text-white placeholder:text-white/20 outline-none focus:border-white/35 transition"
                        />
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.25em] text-white/50 mb-2">Ciudad</label>
                        <input
                            v-model="form.city"
                            type="text"
                            class="w-full h-11 border border-white/12 bg-white/[0.05] px-4 text-sm text-white placeholder:text-white/20 outline-none focus:border-white/35 transition"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.25em] text-white/50 mb-2">Correo</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full h-11 border border-white/12 bg-white/[0.05] px-4 text-sm text-white placeholder:text-white/20 outline-none focus:border-white/35 transition"
                    />
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.25em] text-white/50 mb-2">Tipo de proyecto</label>
                    <select
                        v-model="form.project_type"
                        class="w-full h-11 border border-white/12 bg-[#0D0D0D] px-4 text-sm text-white outline-none focus:border-white/35 transition"
                    >
                        <option value="" class="bg-[#0D0D0D]">Seleccionar</option>
                        <option value="kitchen" class="bg-[#0D0D0D]">Cocina</option>
                        <option value="bathroom" class="bg-[#0D0D0D]">Baño</option>
                        <option value="residential" class="bg-[#0D0D0D]">Residencial</option>
                        <option value="commercial" class="bg-[#0D0D0D]">Comercial</option>
                        <option value="exterior" class="bg-[#0D0D0D]">Exterior</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.25em] text-white/50 mb-2">Mensaje</label>
                    <textarea
                        v-model="form.message"
                        rows="3"
                        class="w-full border border-white/12 bg-white/[0.05] px-4 py-3 text-sm text-white placeholder:text-white/20 outline-none focus:border-white/35 transition resize-none"
                        placeholder="Cuéntanos sobre tu proyecto…"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full h-12 bg-[#CC1A1A] text-white text-sm font-bold uppercase tracking-[0.2em] hover:bg-[#E01F1F] transition disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Enviando…' : 'Enviar solicitud' }}
                </button>

            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    environment: { type: Object, required: true },
    selectedMaterials: { type: Object, default: () => ({}) },
});

defineEmits(['close']);

const materialsList = computed(() => Object.values(props.selectedMaterials || {}));

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
        onSuccess: () => emit('close'),
    });
}
</script>
