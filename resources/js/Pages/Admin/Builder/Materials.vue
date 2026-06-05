<template>
    <BuilderLayout title="Materiales">
        <div class="mb-6 flex justify-between gap-4">
            <p class="text-white/50">
                Administra texturas, miniaturas y propiedades visuales.
            </p>

            <button class="rounded-2xl bg-violet-500 px-5 py-3 font-semibold" @click="openCreate">
                + Nuevo material
            </button>
        </div>

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
            <div
                v-for="item in items"
                :key="item.id"
                class="rounded-3xl border border-white/10 bg-white/[0.06] overflow-hidden"
            >
                <img
                    :src="item.thumbnail_url || item.texture_url"
                    class="h-52 w-full object-cover bg-white/5"
                />

                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-lg">{{ item.name }}</h3>
                            <p class="text-sm text-white/50">{{ item.category?.name }}</p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs" :class="item.is_active ? 'bg-emerald-500/20 text-emerald-200' : 'bg-red-500/20 text-red-200'">
                            {{ item.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <p class="mt-3 text-sm text-white/50">
                        {{ item.finish || 'Sin acabado' }} · {{ item.base_color || 'Sin color' }}
                    </p>

                    <div class="mt-5 flex gap-3">
                        <button class="text-violet-300" @click="openEdit(item)">Editar</button>
                        <button class="text-red-300" @click="destroy(item)">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="drawerOpen" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/60" @click="drawerOpen = false"></div>

            <div class="absolute right-0 top-0 h-full w-full max-w-2xl overflow-y-auto bg-[#15111D] border-l border-white/10 p-6">
                <div class="flex justify-between">
                    <h2 class="text-2xl font-bold">{{ editingItem ? 'Editar material' : 'Nuevo material' }}</h2>
                    <button class="h-10 w-10 rounded-full bg-white/10" @click="drawerOpen = false">×</button>
                </div>

                <form class="mt-6 grid md:grid-cols-2 gap-5" @submit.prevent="submit">
                    <div>
                        <label class="label">Categoría</label>
                        <select v-model="form.material_category_id" class="input">
                            <option value="">Seleccionar</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Nombre</label>
                        <input v-model="form.name" class="input" @input="autoSlug" />
                    </div>

                    <div>
                        <label class="label">Slug</label>
                        <input v-model="form.slug" class="input" @input="slugTouched = true" />
                    </div>

                    <div>
                        <label class="label">SKU</label>
                        <input v-model="form.sku" class="input" />
                    </div>

                    <div>
                        <label class="label">País de origen</label>
                        <input v-model="form.origin_country" class="input" />
                    </div>

                    <div>
                        <label class="label">Acabado</label>
                        <input v-model="form.finish" class="input" />
                    </div>

                    <div>
                        <label class="label">Color base</label>
                        <input v-model="form.base_color" class="input" />
                    </div>

                    <div>
                        <label class="label">Orden</label>
                        <input v-model="form.sort_order" type="number" class="input" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="label">Descripción corta</label>
                        <textarea v-model="form.short_description" class="input min-h-24"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="label">Keywords / Tags</label>
                        <TagInput v-model="form.keywords" placeholder="Agregar keyword…" />
                    </div>

                    <ImageUploader
                        label="Textura"
                        v-model="form.texture_image"
                        :current-url="editingItem?.texture_url"
                        @remove="form.remove_texture_image = true"
                    />

                    <ImageUploader
                        label="Miniatura"
                        v-model="form.thumbnail_image"
                        :current-url="editingItem?.thumbnail_url"
                        @remove="form.remove_thumbnail_image = true"
                    />

                    <div>
                        <label class="label">Escala</label>
                        <input v-model="form.default_scale" type="number" step="0.01" class="input" />
                    </div>

                    <div>
                        <label class="label">Opacidad</label>
                        <input v-model="form.default_opacity" type="number" step="0.01" min="0" max="1" class="input" />
                    </div>

                    <label class="flex items-center gap-3">
                        <input v-model="form.is_featured" type="checkbox" />
                        <span>Destacado</span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input v-model="form.is_active" type="checkbox" />
                        <span>Activo</span>
                    </label>

                    <button class="md:col-span-2 rounded-2xl bg-violet-500 py-3 font-semibold">
                        Guardar material
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
import TagInput from '@/Components/AdminBuilder/TagInput.vue';

const props = defineProps({
    items: Array,
    categories: Array,
});

const drawerOpen = ref(false);
const editingItem = ref(null);

const slugTouched = ref(false);

function toSlug(value) {
    return value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')  // elimina acentos
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/[\s_]+/g, '-')
        .replace(/-+/g, '-');
}

function autoSlug() {
    if (!slugTouched.value) {
        form.slug = toSlug(form.name);
    }
}

const form = useForm({
    material_category_id: '',
    name: '',
    slug: '',
    sku: '',
    origin_country: '',
    finish: '',
    base_color: '',
    short_description: '',
    description: '',
    keywords: [],
    texture_image: null,
    thumbnail_image: null,
    remove_texture_image: false,
    remove_thumbnail_image: false,
    default_scale: 1,
    default_rotation: 0,
    default_opacity: 1,
    is_featured: false,
    is_active: true,
    sort_order: 0,
});

function resetForm() {
    form.reset();
    form.clearErrors();
}

function openCreate() {
    editingItem.value = null;
    slugTouched.value = false;
    resetForm();
    drawerOpen.value = true;
}

function openEdit(item) {
    editingItem.value = item;
    slugTouched.value = true; // al editar, no sobreescribir el slug existente
    resetForm();

    Object.assign(form, {
        material_category_id: item.material_category_id,
        name: item.name,
        slug: item.slug,
        sku: item.sku,
        origin_country: item.origin_country,
        finish: item.finish,
        base_color: item.base_color,
        short_description: item.short_description,
        keywords: item.keywords ?? [],
        description: item.description,
        default_scale: item.default_scale || 1,
        default_rotation: item.default_rotation || 0,
        default_opacity: item.default_opacity || 1,
        is_featured: Boolean(item.is_featured),
        is_active: Boolean(item.is_active),
        sort_order: item.sort_order || 0,
    });

    drawerOpen.value = true;
}

function submit() {
    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            drawerOpen.value = false;
            toast.success(editingItem.value ? 'Material actualizado.' : 'Material creado.', '¡Listo!');
        },
        onError: (errors) => toast.error(Object.values(errors)[0] ?? 'Revisa el formulario.', 'Error'),
    };

    if (editingItem.value) {
        form.post(`/admin/builder/materials/${editingItem.value.id}`, options);
    } else {
        form.post('/admin/builder/materials', options);
    }
}

function destroy(item) {
    if (!confirm(`¿Eliminar "${item.name}"?`)) return;

    router.delete(`/admin/builder/materials/${item.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success(`"${item.name}" eliminado.`, 'Eliminado'),
        onError:   () => toast.error('No se pudo eliminar.', 'Error'),
    });
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
.label {
    display: block;
    margin-bottom: .5rem;
    font-size: .875rem;
    font-weight: 600;
    color: rgba(255,255,255,.8);
}
</style>
