<template>
    <BuilderLayout title="Grupos de zonas">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-white/50">
                    Agrupa zonas de un mismo ambiente para que compartan el mismo material en el decorador.
                </p>
            </div>

            <button
                class="rounded-2xl bg-violet-500 px-5 py-3 font-semibold text-white hover:bg-violet-600"
                @click="openCreate"
            >
                + Nuevo grupo
            </button>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.06] overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/[0.04] text-xs uppercase tracking-wider text-white/40">
                <tr>
                    <th class="p-4">Color</th>
                    <th class="p-4">Grupo</th>
                    <th class="p-4">Ambiente</th>
                    <th class="p-4">Zonas</th>
                    <th class="p-4">Activo</th>
                    <th class="p-4 text-right">Acciones</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                <tr
                    v-for="item in items"
                    :key="item.id"
                    class="hover:bg-white/[0.03]"
                >
                    <td class="p-4">
                        <span
                            class="inline-block h-6 w-6 rounded-full border border-white/20"
                            :style="{ backgroundColor: item.color || '#CC1A1A' }"
                        />
                    </td>

                    <td class="p-4">
                        <p class="font-semibold">{{ item.name }}</p>
                        <p class="text-xs text-white/40">{{ item.slug }}</p>
                    </td>

                    <td class="p-4 text-white/60">
                        {{ item.environment?.name || 'Sin ambiente' }}
                    </td>

                    <td class="p-4">
                        <div v-if="item.zones?.length" class="flex flex-wrap gap-1">
                            <span
                                v-for="zone in item.zones"
                                :key="zone.id"
                                class="rounded-full px-2 py-0.5 text-xs"
                                :style="{ backgroundColor: (item.color || '#CC1A1A') + '33', color: item.color || '#CC1A1A' }"
                            >
                                {{ zone.name }}
                            </span>
                        </div>
                        <span v-else class="text-xs text-white/30">Sin zonas</span>
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
                        No hay grupos registrados.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Drawer -->
        <div v-if="drawerOpen" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/60" @click="drawerOpen = false" />

            <div class="absolute right-0 top-0 h-full w-full max-w-xl overflow-y-auto border-l border-white/10 bg-[#15111D] p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-violet-300">Grupo de zonas</p>
                        <h2 class="text-2xl font-bold">{{ editingItem ? 'Editar grupo' : 'Nuevo grupo' }}</h2>
                    </div>
                    <button class="h-10 w-10 rounded-full bg-white/10 text-xl" @click="drawerOpen = false">×</button>
                </div>

                <form class="mt-6 grid gap-5 md:grid-cols-2" @submit.prevent="submit">
                    <div class="md:col-span-2">
                        <label class="label">Ambiente</label>
                        <select v-model="form.environment_id" class="input" @change="form.zone_ids = []">
                            <option value="">Seleccionar ambiente</option>
                            <option v-for="env in environments" :key="env.id" :value="env.id">
                                {{ env.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.environment_id" class="error">{{ form.errors.environment_id }}</p>
                    </div>

                    <div>
                        <label class="label">Nombre del grupo</label>
                        <input v-model="form.name" class="input" type="text" placeholder="Pisos" @input="autoSlug" />
                        <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="label">Slug</label>
                        <input v-model="form.slug" class="input" type="text" placeholder="pisos" @input="slugTouched = true" />
                    </div>

                    <div>
                        <label class="label">Color identificador</label>
                        <div class="flex items-center gap-3">
                            <input v-model="form.color" type="color" class="h-10 w-14 cursor-pointer rounded-xl border border-white/10 bg-transparent p-1" />
                            <input v-model="form.color" class="input flex-1" type="text" placeholder="#CC1A1A" />
                        </div>
                    </div>

                    <div>
                        <label class="label">Orden</label>
                        <input v-model="form.sort_order" class="input" type="number" min="0" />
                    </div>

                    <!-- Zonas disponibles del ambiente seleccionado -->
                    <div class="md:col-span-2">
                        <label class="label">Zonas del grupo</label>

                        <div v-if="!selectedEnvironmentZones.length" class="rounded-2xl border border-dashed border-white/10 p-4 text-sm text-white/30">
                            Selecciona un ambiente para ver sus zonas.
                        </div>

                        <div v-else class="grid grid-cols-2 gap-2">
                            <label
                                v-for="zone in selectedEnvironmentZones"
                                :key="zone.id"
                                class="flex cursor-pointer items-center gap-3 rounded-2xl border p-3 transition"
                                :class="form.zone_ids.includes(zone.id)
                                    ? 'border-violet-500 bg-violet-500/10'
                                    : 'border-white/10 bg-white/[0.04] hover:border-white/25'"
                            >
                                <input
                                    type="checkbox"
                                    :value="zone.id"
                                    v-model="form.zone_ids"
                                    class="h-4 w-4 accent-violet-500"
                                />
                                <span class="text-sm font-medium">{{ zone.name }}</span>
                            </label>
                        </div>
                    </div>

                    <label class="flex items-center gap-3">
                        <input v-model="form.is_active" type="checkbox" class="h-5 w-5" />
                        <span class="text-sm text-white/80">Activo</span>
                    </label>

                    <button
                        type="submit"
                        class="md:col-span-2 rounded-2xl bg-violet-500 py-3 font-semibold text-white hover:bg-violet-600 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar grupo' }}
                    </button>
                </form>
            </div>
        </div>
    </BuilderLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import BuilderLayout from '@/Components/AdminBuilder/BuilderLayout.vue';
import { useToast } from '@/composables/useToast.js';

const toast = useToast();

const props = defineProps({
    items:        { type: Array, default: () => [] },
    environments: { type: Array, default: () => [] },
});

const drawerOpen  = ref(false);
const editingItem = ref(null);
const slugTouched = ref(false);

const form = useForm({
    environment_id: '',
    name:           '',
    slug:           '',
    color:          '#CC1A1A',
    icon:           '',
    is_active:      true,
    sort_order:     0,
    zone_ids:       [],
});

const selectedEnvironmentZones = computed(() => {
    const env = props.environments.find((e) => Number(e.id) === Number(form.environment_id));
    return env?.zones || [];
});

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

function resetForm() {
    form.clearErrors();
    form.environment_id = '';
    form.name           = '';
    form.slug           = '';
    form.color          = '#CC1A1A';
    form.icon           = '';
    form.is_active      = true;
    form.sort_order     = 0;
    form.zone_ids       = [];
}

function openCreate() {
    editingItem.value = null;
    slugTouched.value = false;
    resetForm();
    drawerOpen.value  = true;
}

function openEdit(item) {
    editingItem.value   = item;
    slugTouched.value   = true;
    resetForm();

    form.environment_id = item.environment_id || '';
    form.name           = item.name || '';
    form.slug           = item.slug || '';
    form.color          = item.color || '#CC1A1A';
    form.icon           = item.icon || '';
    form.is_active      = Boolean(item.is_active);
    form.sort_order     = item.sort_order || 0;
    form.zone_ids       = (item.zones || []).map((z) => z.id);

    drawerOpen.value = true;
}

function submit() {
    const url = editingItem.value
        ? `/admin/builder/environment-zone-groups/${editingItem.value.id}`
        : '/admin/builder/environment-zone-groups';

    form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            drawerOpen.value = false;
            toast.success(
                editingItem.value ? 'Grupo actualizado.' : 'Grupo creado.',
                editingItem.value ? 'Actualizado' : 'Creado',
            );
        },
        onError: (errors) => {
            const first = Object.values(errors)[0];
            toast.error(first ?? 'Revisa los campos del formulario.', 'Error al guardar');
        },
    });
}

function destroy(item) {
    if (!confirm(`¿Eliminar el grupo "${item.name}"? Las zonas quedarán sin grupo.`)) return;

    router.delete(`/admin/builder/environment-zone-groups/${item.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success('Grupo eliminado.', 'Eliminado'),
        onError:   () => toast.error('No se pudo eliminar el grupo.', 'Error'),
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
.input:focus { border-color: rgb(139 92 246); }
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
