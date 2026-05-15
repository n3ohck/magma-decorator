<template>
    <BuilderLayout title="Ambientes">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-white/50">
                    Administra los espacios donde se aplicarán materiales: cocinas, baños, salas, exteriores y áreas comerciales.
                </p>
            </div>

            <button
                class="rounded-2xl bg-violet-500 px-5 py-3 font-semibold text-white hover:bg-violet-600"
                @click="openCreate"
            >
                + Nuevo ambiente
            </button>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="item in items"
                :key="item.id"
                class="overflow-hidden rounded-3xl border border-white/10 bg-white/[0.06]"
            >
                <div class="relative h-56 bg-white/5">
                    <img
                        v-if="item.preview_image_url || item.base_image_url"
                        :src="item.preview_image_url || item.base_image_url"
                        class="h-full w-full object-cover"
                        :alt="item.name"
                    />

                    <div
                        v-else
                        class="flex h-full items-center justify-center text-white/30"
                    >
                        Sin imagen
                    </div>

                    <div class="absolute left-4 top-4 flex gap-2">
                        <span
                            class="rounded-full px-3 py-1 text-xs backdrop-blur"
                            :class="item.is_active ? 'bg-emerald-500/80 text-white' : 'bg-red-500/80 text-white'"
                        >
                            {{ item.is_active ? 'Activo' : 'Inactivo' }}
                        </span>

                        <span
                            v-if="item.is_featured"
                            class="rounded-full bg-amber-500/80 px-3 py-1 text-xs text-white backdrop-blur"
                        >
                            Destacado
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">
                                {{ item.name }}
                            </h3>

                            <p class="text-sm text-white/50">
                                {{ typeLabel(item.type) }} · {{ item.canvas_width }} × {{ item.canvas_height }}
                            </p>
                        </div>

                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-white/60">
                            {{ item.zones_count || 0 }} zonas
                        </span>
                    </div>

                    <p class="mt-3 line-clamp-2 text-sm text-white/50">
                        {{ item.description || 'Sin descripción.' }}
                    </p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a
                            :href="`/decorador/${item.slug}`"
                            target="_blank"
                            class="rounded-xl border border-white/10 px-3 py-2 text-sm text-white/70 hover:bg-white/10"
                        >
                            Ver público
                        </a>

                        <button
                            class="rounded-xl border border-violet-400/20 px-3 py-2 text-sm text-violet-300 hover:bg-violet-500/10"
                            @click="openEdit(item)"
                        >
                            Editar
                        </button>

                        <button
                            class="rounded-xl border border-red-400/20 px-3 py-2 text-sm text-red-300 hover:bg-red-500/10"
                            @click="destroy(item)"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="!items.length"
                class="rounded-3xl border border-dashed border-white/10 bg-white/[0.04] p-10 text-center text-white/50 md:col-span-2 xl:col-span-3"
            >
                Todavía no hay ambientes registrados.
            </div>
        </div>

        <div v-if="drawerOpen" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/60" @click="drawerOpen = false"></div>

            <div class="absolute right-0 top-0 h-full w-full max-w-3xl overflow-y-auto border-l border-white/10 bg-[#15111D] p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-violet-300">
                            Ambiente
                        </p>

                        <h2 class="text-2xl font-bold">
                            {{ editingItem ? 'Editar ambiente' : 'Nuevo ambiente' }}
                        </h2>

                        <p class="mt-1 text-sm text-white/50">
                            La imagen base, las máscaras y los overlays deben tener el mismo tamaño.
                        </p>
                    </div>

                    <button
                        class="h-10 w-10 rounded-full bg-white/10 text-xl"
                        @click="drawerOpen = false"
                    >
                        ×
                    </button>
                </div>

                <form class="mt-6 grid gap-5 md:grid-cols-2" @submit.prevent="submit">
                    <div>
                        <label class="label">Nombre</label>
                        <input v-model="form.name" class="input" type="text" />
                        <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="label">Slug</label>
                        <input v-model="form.slug" class="input" type="text" placeholder="cocina-premium-01" />
                        <p v-if="form.errors.slug" class="error">{{ form.errors.slug }}</p>
                    </div>

                    <div>
                        <label class="label">Tipo de ambiente</label>
                        <select v-model="form.type" class="input">
                            <option value="">Seleccionar</option>
                            <option value="kitchen">Cocina</option>
                            <option value="bathroom">Baño</option>
                            <option value="living_room">Sala</option>
                            <option value="commercial">Comercial</option>
                            <option value="exterior">Exterior</option>
                            <option value="office">Oficina</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Orden</label>
                        <input v-model="form.sort_order" class="input" type="number" min="0" />
                    </div>

                    <div>
                        <label class="label">Ancho canvas</label>
                        <input v-model="form.canvas_width" class="input" type="number" min="100" />
                    </div>

                    <div>
                        <label class="label">Alto canvas</label>
                        <input v-model="form.canvas_height" class="input" type="number" min="100" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="label">Descripción</label>
                        <textarea
                            v-model="form.description"
                            class="input min-h-28"
                            placeholder="Ejemplo: Cocina contemporánea con barra e isla central."
                        ></textarea>
                    </div>

                    <ImageUploader
                        label="Imagen base del ambiente"
                        v-model="form.base_image"
                        :current-url="editingItem?.base_image_url"
                        @remove="form.remove_base_image = true"
                    />

                    <ImageUploader
                        label="Imagen preview"
                        v-model="form.preview_image"
                        :current-url="editingItem?.preview_image_url"
                        @remove="form.remove_preview_image = true"
                    />

                    <ImageUploader
                        label="Overlay de sombras"
                        v-model="form.shadow_overlay_image"
                        :current-url="editingItem?.shadow_overlay_url"
                        @remove="form.remove_shadow_overlay_image = true"
                    />

                    <ImageUploader
                        label="Overlay de luces"
                        v-model="form.light_overlay_image"
                        :current-url="editingItem?.light_overlay_url"
                        @remove="form.remove_light_overlay_image = true"
                    />

                    <ImageUploader
                        label="Overlay frontal / objetos encima"
                        v-model="form.foreground_overlay_image"
                        :current-url="editingItem?.foreground_overlay_url"
                        @remove="form.remove_foreground_overlay_image = true"
                    />

                    <label class="flex items-center gap-3">
                        <input v-model="form.is_featured" type="checkbox" class="h-5 w-5" />
                        <span class="text-sm text-white/80">Ambiente destacado</span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input v-model="form.is_active" type="checkbox" class="h-5 w-5" />
                        <span class="text-sm text-white/80">Activo</span>
                    </label>

                    <div class="md:col-span-2 rounded-2xl border border-amber-400/20 bg-amber-500/10 p-4 text-sm text-amber-100">
                        Recomendación: usa una imagen base de alta calidad, por ejemplo 1600×1000 o 1920×1200.
                        Las máscaras PNG de las zonas deben tener exactamente el mismo tamaño.
                    </div>

                    <button
                        type="submit"
                        class="md:col-span-2 rounded-2xl bg-violet-500 py-3 font-semibold text-white hover:bg-violet-600 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar ambiente' }}
                    </button>
                </form>
            </div>
        </div>
    </BuilderLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import BuilderLayout from '@/Components/AdminBuilder/BuilderLayout.vue';
import ImageUploader from '@/Components/AdminBuilder/ImageUploader.vue';

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});

const drawerOpen = ref(false);
const editingItem = ref(null);

const form = useForm({
    name: '',
    slug: '',
    type: '',
    description: '',
    base_image: null,
    preview_image: null,
    shadow_overlay_image: null,
    light_overlay_image: null,
    remove_base_image: false,
    remove_preview_image: false,
    remove_shadow_overlay_image: false,
    remove_light_overlay_image: false,
    canvas_width: 1600,
    canvas_height: 1000,
    is_featured: false,
    is_active: true,
    sort_order: 0,
    foreground_overlay_image: null,
    remove_foreground_overlay_image: false,
});

function resetForm() {
    form.clearErrors();
    form.reset();

    form.name = '';
    form.slug = '';
    form.type = '';
    form.description = '';
    form.base_image = null;
    form.preview_image = null;
    form.shadow_overlay_image = null;
    form.light_overlay_image = null;
    form.remove_base_image = false;
    form.remove_preview_image = false;
    form.remove_shadow_overlay_image = false;
    form.remove_light_overlay_image = false;
    form.canvas_width = 1600;
    form.canvas_height = 1000;
    form.is_featured = false;
    form.is_active = true;
    form.sort_order = 0;
    form.foreground_overlay_image = null;
    form.remove_foreground_overlay_image = false;
}

function openCreate() {
    editingItem.value = null;
    resetForm();
    drawerOpen.value = true;
}

function openEdit(item) {
    editingItem.value = item;
    resetForm();

    form.name = item.name || '';
    form.slug = item.slug || '';
    form.type = item.type || '';
    form.description = item.description || '';
    form.canvas_width = item.canvas_width || 1600;
    form.canvas_height = item.canvas_height || 1000;
    form.is_featured = Boolean(item.is_featured);
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
        },
    };

    if (editingItem.value) {
        form.post(`/admin/builder/environments/${editingItem.value.id}`, options);
    } else {
        form.post('/admin/builder/environments', options);
    }
}

function destroy(item) {
    if (!confirm(`¿Eliminar el ambiente "${item.name}"? También se eliminarán sus zonas.`)) return;

    router.delete(`/admin/builder/environments/${item.id}`, {
        preserveScroll: true,
    });
}

function typeLabel(type) {
    const labels = {
        kitchen: 'Cocina',
        bathroom: 'Baño',
        living_room: 'Sala',
        commercial: 'Comercial',
        exterior: 'Exterior',
        office: 'Oficina',
        other: 'Otro',
    };

    return labels[type] || 'Ambiente';
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
