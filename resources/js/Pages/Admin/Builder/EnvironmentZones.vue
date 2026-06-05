<template>
    <BuilderLayout title="Zonas de ambientes">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-white/50">
                    Define las áreas editables de cada ambiente: piso, muro, barra, isla, backsplash o fachada.
                </p>
            </div>

            <button
                class="rounded-2xl bg-violet-500 px-5 py-3 font-semibold text-white hover:bg-violet-600"
                @click="openCreate"
            >
                + Nueva zona
            </button>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_420px]">
            <div class="rounded-3xl border border-white/10 bg-white/[0.06] overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-white/[0.04] text-xs uppercase tracking-wider text-white/40">
                    <tr>
                        <th class="p-4">Máscara</th>
                        <th class="p-4">Zona</th>
                        <th class="p-4">Ambiente</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Activa</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                    <tr
                        v-for="item in items"
                        :key="item.id"
                        class="hover:bg-white/[0.03]"
                        @mouseenter="previewItem = item"
                    >
                        <td class="p-4">
                            <img
                                v-if="item.mask_image_url"
                                :src="item.mask_image_url"
                                class="h-14 w-20 rounded-xl bg-white/10 object-cover"
                            />
                            <div v-else class="grid h-14 w-20 place-items-center rounded-xl bg-white/10 text-white/30">
                                -
                            </div>
                        </td>

                        <td class="p-4">
                            <p class="font-semibold">{{ item.name }}</p>
                            <p class="text-xs text-white/40">{{ item.slug }}</p>
                        </td>

                        <td class="p-4 text-white/60">
                            {{ item.environment?.name || 'Sin ambiente' }}
                        </td>

                        <td class="p-4 text-white/60">
                            {{ zoneTypeLabel(item.zone_type) }}
                        </td>

                        <td class="p-4">
                                <span
                                    class="rounded-full px-3 py-1 text-xs"
                                    :class="item.is_active ? 'bg-emerald-500/20 text-emerald-200' : 'bg-red-500/20 text-red-200'"
                                >
                                    {{ item.is_active ? 'Sí' : 'No' }}
                                </span>
                        </td>

                        <td class="p-4 text-right">
                            <button class="mr-4 text-violet-300 hover:text-violet-100" @click="openEdit(item)">
                                Editar
                            </button>

                            <button class="text-red-300 hover:text-red-100" @click="destroy(item)">
                                Eliminar
                            </button>
                        </td>
                    </tr>

                    <tr v-if="!items.length">
                        <td colspan="6" class="p-8 text-center text-white/50">
                            No hay zonas registradas.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <aside class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
                <p class="text-xs uppercase tracking-[0.25em] text-violet-300">
                    Preview
                </p>

                <h3 class="mt-1 text-lg font-bold">
                    {{ previewItem?.name || 'Selecciona una zona' }}
                </h3>

                <p class="text-sm text-white/50">
                    {{ previewItem?.environment?.name || 'Pasa el mouse sobre una zona para verla.' }}
                </p>

                <div class="mt-5 overflow-hidden rounded-2xl bg-black/30">
                    <div v-if="previewItem?.environment?.base_image_url" class="relative">
                        <img
                            :src="previewItem.environment.base_image_url"
                            class="w-full object-cover"
                        />

                        <img
                            v-if="previewItem.mask_image_url"
                            :src="previewItem.mask_image_url"
                            class="absolute inset-0 h-full w-full object-cover opacity-60 mix-blend-screen"
                        />
                    </div>

                    <div v-else class="grid h-72 place-items-center text-white/30">
                        Sin preview disponible
                    </div>
                </div>

                <div v-if="previewItem" class="mt-5 space-y-2 text-sm text-white/60">
                    <p>
                        <span class="text-white/40">Tipo:</span>
                        {{ zoneTypeLabel(previewItem.zone_type) }}
                    </p>
                    <p>
                        <span class="text-white/40">Escala:</span>
                        {{ previewItem.default_texture_scale }}
                    </p>
                    <p>
                        <span class="text-white/40">Opacidad:</span>
                        {{ previewItem.default_opacity }}
                    </p>
                </div>
            </aside>
        </div>

        <div v-if="drawerOpen" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/60" @click="drawerOpen = false"></div>

            <div class="absolute right-0 top-0 h-full w-full max-w-3xl overflow-y-auto border-l border-white/10 bg-[#15111D] p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-violet-300">
                            Zona editable
                        </p>

                        <h2 class="text-2xl font-bold">
                            {{ editingItem ? 'Editar zona' : 'Nueva zona' }}
                        </h2>

                        <p class="mt-1 text-sm text-white/50">
                            La máscara PNG debe tener el mismo tamaño que la imagen base del ambiente.
                        </p>
                    </div>

                    <button class="h-10 w-10 rounded-full bg-white/10 text-xl" @click="drawerOpen = false">
                        ×
                    </button>
                </div>

                <form class="mt-6 grid gap-5 md:grid-cols-2" @submit.prevent="submit">
                    <div class="md:col-span-2">
                        <label class="label">Ambiente</label>
                        <select v-model="form.environment_id" class="input">
                            <option value="">Seleccionar ambiente</option>
                            <option v-for="environment in environments" :key="environment.id" :value="environment.id">
                                {{ environment.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.environment_id" class="error">{{ form.errors.environment_id }}</p>
                    </div>

                    <div>
                        <label class="label">Nombre de zona</label>
                        <input v-model="form.name" class="input" type="text" placeholder="Piso" @input="autoSlug" />
                        <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="label">Slug</label>
                        <input v-model="form.slug" class="input" type="text" placeholder="piso" @input="slugTouched = true" />
                    </div>

                    <div>
                        <label class="label">Tipo de zona</label>
                        <select v-model="form.zone_type" class="input">
                            <option value="">Seleccionar</option>
                            <option value="floor">Piso</option>
                            <option value="wall">Muro</option>
                            <option value="countertop">Cubierta / Barra</option>
                            <option value="backsplash">Backsplash</option>
                            <option value="island">Isla</option>
                            <option value="facade">Fachada</option>
                            <option value="shower_wall">Muro de ducha</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Orden</label>
                        <input v-model="form.sort_order" class="input" type="number" min="0" />
                    </div>

                    <div>
                        <label class="label">Escala textura</label>
                        <input v-model="form.default_texture_scale" class="input" type="number" step="0.01" min="0.1" />
                    </div>

                    <div>
                        <label class="label">Rotación textura</label>
                        <input v-model="form.default_texture_rotation" class="input" type="number" step="0.01" />
                    </div>

                    <div>
                        <label class="label">Opacidad</label>
                        <input v-model="form.default_opacity" class="input" type="number" step="0.01" min="0" max="1" />
                    </div>

                    <div class="flex items-center gap-3 pt-8">
                        <input v-model="form.supports_perspective" type="checkbox" class="h-5 w-5" />
                        <label class="text-sm text-white/80">Soporta perspectiva avanzada</label>
                    </div>

                    <!-- Editor de máscara (polígono + SAM) -->
                    <div v-if="selectedEnvironment?.base_image_url" class="md:col-span-2">
                        <div class="rounded-2xl border border-violet-500/20 bg-violet-500/5 p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-sm font-semibold text-violet-300">✦ Editor de máscara</span>
                                <span class="rounded-full bg-violet-500/20 px-2 py-0.5 text-[10px] text-violet-300 font-semibold uppercase tracking-wider">Polígono / SAM 2</span>
                            </div>

                            <MaskEditor
                                :image-url="selectedEnvironment.base_image_url"
                                :natural-width="selectedEnvironment.canvas_width || 0"
                                :natural-height="selectedEnvironment.canvas_height || 0"
                                @mask-ready="onMaskReady"
                            />

                            <div v-if="samAppliedMaskUrl" class="mt-3 flex items-center gap-2">
                                <span class="text-xs text-emerald-300">✓ Máscara aplicada</span>
                                <button
                                    type="button"
                                    class="text-xs text-white/40 hover:underline"
                                    @click="samAppliedMaskUrl = null; form.sam_mask_path = ''"
                                >
                                    Quitar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <ImageUploader
                            label="Máscara PNG"
                            v-model="form.mask_image"
                            :current-url="samAppliedMaskUrl || editingItem?.mask_image_url"
                            @remove="form.remove_mask_image = true; form.sam_mask_path = ''; samAppliedMaskUrl = null"
                        />
                    </div>

                    <div class="md:col-span-2 grid gap-5 lg:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                            <p class="mb-3 text-sm font-semibold text-white/80">Preview de ambiente</p>

                            <div class="overflow-hidden rounded-xl bg-black/30">
                                <img
                                    v-if="selectedEnvironment?.base_image_url"
                                    :src="selectedEnvironment.base_image_url"
                                    class="w-full object-cover"
                                />
                                <div v-else class="grid h-48 place-items-center text-white/30">
                                    Selecciona un ambiente
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-amber-400/20 bg-amber-500/10 p-4 text-sm text-amber-100">
                            <p class="font-semibold">Tip para máscaras</p>
                            <p class="mt-2">
                                Crea una imagen PNG del mismo tamaño que la base.
                                La zona editable debe estar visible y el resto transparente.
                            </p>
                            <p class="mt-2">
                                Ejemplo: si la base mide 1600×1000, la máscara también debe medir 1600×1000.
                            </p>
                        </div>
                    </div>


                    <label class="flex items-center gap-3">
                        <input v-model="form.is_active" type="checkbox" class="h-5 w-5" />
                        <span class="text-sm text-white/80">Activa</span>
                    </label>

                    <button
                        type="submit"
                        class="md:col-span-2 rounded-2xl bg-violet-500 py-3 font-semibold text-white hover:bg-violet-600 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar zona' }}
                    </button>
                </form>
            </div>
        </div>
    </BuilderLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import BuilderLayout from '@/Components/AdminBuilder/BuilderLayout.vue';
import ImageUploader from '@/Components/AdminBuilder/ImageUploader.vue';
import MaskEditor from '@/Components/AdminBuilder/MaskEditor.vue';
import { useToast } from '@/composables/useToast.js';

const toast = useToast();

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    environments: {
        type: Array,
        default: () => [],
    },
});

const drawerOpen = ref(false);
const editingItem = ref(null);
const previewItem = ref(props.items?.[0] || null);

// SAM state
const samImageRef = ref(null);
const samBaseImageRef = ref(null);
const samState = ref('idle');    // idle | loading | done | error
const samClickPoint = ref(null); // { displayX, displayY, fracX, fracY }
const samMaskUrl = ref(null);
const samMaskPath = ref(null);
const samAppliedMaskUrl = ref(null);
const samError = ref('');

async function handleSamClick(event) {
    if (samState.value === 'loading') return;
    if (!selectedEnvironment.value?.base_image_url) return;

    const rect = event.currentTarget.getBoundingClientRect();
    const displayX = event.clientX - rect.left;
    const displayY = event.clientY - rect.top;
    const fracX = displayX / rect.width;
    const fracY = displayY / rect.height;

    samClickPoint.value = { displayX, displayY, fracX, fracY };
    samMaskUrl.value = null;
    samMaskPath.value = null;
    samError.value = '';
    samState.value = 'loading';

    // Get the natural image dimensions from the img element
    const imgEl = samBaseImageRef.value;
    const naturalW = imgEl?.naturalWidth || selectedEnvironment.value.canvas_width || 1200;
    const naturalH = imgEl?.naturalHeight || selectedEnvironment.value.canvas_height || 1200;

    try {
        const { data } = await axios.post('/admin/builder/sam-mask', {
            image_url:    selectedEnvironment.value.base_image_url,
            x:            fracX,
            y:            fracY,
            image_width:  naturalW,
            image_height: naturalH,
        });

        samMaskUrl.value  = data.mask_url;
        samMaskPath.value = data.mask_path;
        samState.value    = 'done';

    } catch (err) {
        samError.value = err?.response?.data?.error ?? 'SAM no pudo generar la máscara.';
        samState.value = 'error';
    }
}

function applySamMask() {
    if (!samMaskUrl.value || !samMaskPath.value) return;
    form.sam_mask_path     = samMaskPath.value;
    form.mask_image        = null;
    form.remove_mask_image = false;
    samAppliedMaskUrl.value = samMaskUrl.value;
}

function onMaskReady({ file, preview_url }) {
    // Assign the File directly to form.mask_image — uses the normal upload path
    // on form submission, bypassing the sam_mask_path string field entirely.
    form.mask_image        = file;
    form.sam_mask_path     = '';
    form.remove_mask_image = false;
    samAppliedMaskUrl.value = preview_url;
}

function clearSam() {
    samState.value     = 'idle';
    samClickPoint.value = null;
    samMaskUrl.value   = null;
    samMaskPath.value  = null;
    samError.value     = '';
}

const slugTouched = ref(false);

function toSlug(value) {
    return value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/[\s_]+/g, '-')
        .replace(/-+/g, '-');
}

function autoSlug() {
    if (!slugTouched.value) form.slug = toSlug(form.name);
}

const form = useForm({
    environment_id: '',
    name: '',
    slug: '',
    zone_type: '',
    mask_image: null,
    remove_mask_image: false,
    polygon_points: '',
    default_texture_scale: 1,
    default_texture_rotation: 0,
    default_opacity: 1,
    supports_perspective: false,
    perspective_points: '',
    sam_mask_path: '',
    is_active: true,
    sort_order: 0,
});

const selectedEnvironment = computed(() => {
    return props.environments.find((environment) => Number(environment.id) === Number(form.environment_id));
});

function resetForm() {
    form.clearErrors();
    form.reset();

    form.environment_id = '';
    form.name = '';
    form.slug = '';
    form.zone_type = '';
    form.mask_image = null;
    form.remove_mask_image = false;
    form.polygon_points = '';
    form.default_texture_scale = 1;
    form.default_texture_rotation = 0;
    form.default_opacity = 1;
    form.supports_perspective = false;
    form.perspective_points = '';
    form.is_active = true;
    form.sort_order = 0;
}

function openCreate() {
    editingItem.value = null;
    slugTouched.value = false;
    resetForm();
    clearSam();
    samAppliedMaskUrl.value = null;
    drawerOpen.value = true;
}

function openEdit(item) {
    editingItem.value = item;
    slugTouched.value = true;
    resetForm();
    clearSam();
    samAppliedMaskUrl.value = null;

    form.environment_id = item.environment_id || '';
    form.name = item.name || '';
    form.slug = item.slug || '';
    form.zone_type = item.zone_type || '';
    form.polygon_points = item.polygon_points ? JSON.stringify(item.polygon_points, null, 2) : '';
    form.default_texture_scale = item.default_texture_scale || 1;
    form.default_texture_rotation = item.default_texture_rotation || 0;
    form.default_opacity = item.default_opacity || 1;
    form.supports_perspective = Boolean(item.supports_perspective);
    form.perspective_points = item.perspective_points ? JSON.stringify(item.perspective_points, null, 2) : '';
    form.sam_mask_path = '';
    form.is_active = Boolean(item.is_active);
    form.sort_order = item.sort_order || 0;

    drawerOpen.value = true;
}

function submit() {
    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            drawerOpen.value = false;
            toast.success(
                editingItem.value ? 'Zona actualizada correctamente.' : 'Zona creada correctamente.',
                editingItem.value ? 'Zona actualizada' : 'Zona creada'
            );
        },
        onError: (errors) => {
            const first = Object.values(errors)[0];
            toast.error(first ?? 'Revisa los campos del formulario.', 'Error al guardar');
        },
    };

    if (editingItem.value) {
        form.post(`/admin/builder/environment-zones/${editingItem.value.id}`, options);
    } else {
        form.post('/admin/builder/environment-zones', options);
    }
}

function destroy(item) {
    if (!confirm(`¿Eliminar la zona "${item.name}"?`)) return;

    router.delete(`/admin/builder/environment-zones/${item.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success('Zona eliminada.', 'Eliminada'),
        onError:   () => toast.error('No se pudo eliminar la zona.', 'Error'),
    });
}

function zoneTypeLabel(type) {
    const labels = {
        floor: 'Piso',
        wall: 'Muro',
        countertop: 'Cubierta / Barra',
        backsplash: 'Backsplash',
        island: 'Isla',
        facade: 'Fachada',
        shower_wall: 'Muro de ducha',
        other: 'Otro',
    };

    return labels[type] || 'Zona';
}
</script>

<style scoped>
.input {
    width: 100%;
    border-radius: 1rem;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.06);
    padding: .85rem 1rem;
    color: white;
    outline: none;
}

.input:focus {
    border-color: rgb(139 92 246);
}

.label {
    display: block;
    margin-bottom: .5rem;
    font-size: .875rem;
    font-weight: 600;
    color: rgba(255,255,255,.8);
}

.error {
    margin-top: .35rem;
    font-size: .8rem;
    color: rgb(252 165 165);
}
</style>
