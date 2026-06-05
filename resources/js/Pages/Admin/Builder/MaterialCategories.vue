<template>
    <BuilderLayout title="Categorías de materiales">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <p class="text-white/50">
                    Administra mármol, cuarzo, granito, cerámicos y demás categorías.
                </p>
            </div>

            <button
                class="rounded-2xl bg-violet-500 px-5 py-3 font-semibold text-white hover:bg-violet-600"
                @click="openCreate"
            >
                + Nueva categoría
            </button>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.06] overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/[0.04] text-xs uppercase tracking-wider text-white/40">
                <tr>
                    <th class="p-4">Imagen</th>
                    <th class="p-4">Nombre</th>
                    <th class="p-4">Slug</th>
                    <th class="p-4">Activa</th>
                    <th class="p-4">Orden</th>
                    <th class="p-4 text-right">Acciones</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                <tr v-for="item in items" :key="item.id">
                    <td class="p-4">
                        <img
                            v-if="item.cover_image_url"
                            :src="item.cover_image_url"
                            class="h-14 w-14 rounded-xl object-cover"
                        />
                        <div v-else class="h-14 w-14 rounded-xl bg-white/10 grid place-items-center text-white/30">
                            -
                        </div>
                    </td>

                    <td class="p-4 font-semibold">
                        {{ item.name }}
                    </td>

                    <td class="p-4 text-white/60">
                        {{ item.slug }}
                    </td>

                    <td class="p-4">
                            <span
                                class="rounded-full px-3 py-1 text-xs"
                                :class="item.is_active ? 'bg-emerald-500/20 text-emerald-200' : 'bg-red-500/20 text-red-200'"
                            >
                                {{ item.is_active ? 'Sí' : 'No' }}
                            </span>
                    </td>

                    <td class="p-4 text-white/60">
                        {{ item.sort_order }}
                    </td>

                    <td class="p-4 text-right">
                        <button class="text-violet-300 hover:text-violet-100 mr-4" @click="openEdit(item)">
                            Editar
                        </button>

                        <button class="text-red-300 hover:text-red-100" @click="destroy(item)">
                            Eliminar
                        </button>
                    </td>
                </tr>

                <tr v-if="!items.length">
                    <td colspan="6" class="p-8 text-center text-white/50">
                        No hay categorías registradas.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="drawerOpen"
            class="fixed inset-0 z-50"
        >
            <div class="absolute inset-0 bg-black/60" @click="drawerOpen = false"></div>

            <div class="absolute right-0 top-0 h-full w-full max-w-xl overflow-y-auto bg-[#15111D] border-l border-white/10 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-violet-300">
                            Categoría
                        </p>
                        <h2 class="text-2xl font-bold">
                            {{ editingItem ? 'Editar categoría' : 'Nueva categoría' }}
                        </h2>
                    </div>

                    <button class="h-10 w-10 rounded-full bg-white/10" @click="drawerOpen = false">
                        ×
                    </button>
                </div>

                <form class="mt-6 space-y-5" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-semibold text-white/80 mb-2">Nombre</label>
                        <input v-model="form.name" class="input" type="text" @input="autoSlug" />
                        <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white/80 mb-2">Slug</label>
                        <input v-model="form.slug" class="input" type="text" @input="slugTouched = true" />
                        <p v-if="form.errors.slug" class="error">{{ form.errors.slug }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white/80 mb-2">Descripción</label>
                        <textarea v-model="form.description" class="input min-h-28"></textarea>
                    </div>

                    <ImageUploader
                        label="Imagen de portada"
                        v-model="form.cover_image"
                        :current-url="editingItem?.cover_image_url"
                        @remove="form.remove_cover_image = true"
                    />

                    <div class="flex items-center gap-3">
                        <input v-model="form.is_active" type="checkbox" class="h-5 w-5" />
                        <label class="text-sm text-white/80">Activa</label>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white/80 mb-2">Orden</label>
                        <input v-model="form.sort_order" class="input" type="number" />
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-violet-500 py-3 font-semibold text-white hover:bg-violet-600 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar' }}
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
    name: '',
    slug: '',
    description: '',
    cover_image: null,
    remove_cover_image: false,
    is_active: true,
    sort_order: 0,
});

function resetForm() {
    form.clearErrors();
    form.reset();

    form.name = '';
    form.slug = '';
    form.description = '';
    form.cover_image = null;
    form.remove_cover_image = false;
    form.is_active = true;
    form.sort_order = 0;
}

function openCreate() {
    editingItem.value = null;
    slugTouched.value = false;
    resetForm();
    drawerOpen.value = true;
}

function openEdit(item) {
    editingItem.value = item;
    slugTouched.value = true;
    resetForm();

    form.name = item.name;
    form.slug = item.slug;
    form.description = item.description;
    form.is_active = Boolean(item.is_active);
    form.sort_order = item.sort_order || 0;

    drawerOpen.value = true;
}

function submit() {
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            drawerOpen.value = false;
        },
    };

    if (editingItem.value) {
        form.post(`/admin/builder/material-categories/${editingItem.value.id}`, options);
    } else {
        form.post('/admin/builder/material-categories', options);
    }
}

function destroy(item) {
    if (!confirm(`¿Eliminar la categoría "${item.name}"?`)) return;

    router.delete(`/admin/builder/material-categories/${item.id}`, {
        preserveScroll: true,
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

.input:focus {
    border-color: rgb(139 92 246);
}

.error {
    margin-top: .35rem;
    font-size: .8rem;
    color: rgb(252 165 165);
}
</style>
