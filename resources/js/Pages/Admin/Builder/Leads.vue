<template>
    <BuilderLayout title="Leads / Solicitudes">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-white/50">
                    Solicitudes generadas desde el decorador virtual.
                </p>
            </div>

            <div class="flex gap-2 overflow-x-auto">
                <button
                    v-for="status in statuses"
                    :key="status.value"
                    class="rounded-full px-4 py-2 text-sm whitespace-nowrap"
                    :class="activeStatus === status.value ? 'bg-violet-500 text-white' : 'bg-white/10 text-white/70'"
                    @click="activeStatus = status.value"
                >
                    {{ status.label }}
                </button>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_420px]">
            <div class="rounded-3xl border border-white/10 bg-white/[0.06] overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-white/[0.04] text-xs uppercase tracking-wider text-white/40">
                    <tr>
                        <th class="p-4">Cliente</th>
                        <th class="p-4">Contacto</th>
                        <th class="p-4">Proyecto</th>
                        <th class="p-4">Ambiente</th>
                        <th class="p-4">Estatus</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                    <tr
                        v-for="item in filteredItems"
                        :key="item.id"
                        class="hover:bg-white/[0.03]"
                        @click="selectedLead = item"
                    >
                        <td class="p-4">
                            <p class="font-semibold">{{ item.name }}</p>
                            <p class="text-xs text-white/40">{{ formatDate(item.created_at) }}</p>
                        </td>

                        <td class="p-4 text-white/60">
                            <p>{{ item.phone || 'Sin teléfono' }}</p>
                            <p class="text-xs">{{ item.email || 'Sin correo' }}</p>
                        </td>

                        <td class="p-4 text-white/60">
                            {{ projectTypeLabel(item.project_type) }}
                        </td>

                        <td class="p-4 text-white/60">
                            {{ item.design_session?.environment?.name || 'Sin diseño' }}
                        </td>

                        <td class="p-4">
                                <span class="rounded-full px-3 py-1 text-xs" :class="statusClass(item.status)">
                                    {{ statusLabel(item.status) }}
                                </span>
                        </td>

                        <td class="p-4 text-right">
                            <button class="mr-4 text-violet-300 hover:text-violet-100" @click.stop="openEdit(item)">
                                Cambiar estatus
                            </button>

                            <button class="text-red-300 hover:text-red-100" @click.stop="destroy(item)">
                                Eliminar
                            </button>
                        </td>
                    </tr>

                    <tr v-if="!filteredItems.length">
                        <td colspan="6" class="p-8 text-center text-white/50">
                            No hay solicitudes para este filtro.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <aside class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
                <p class="text-xs uppercase tracking-[0.25em] text-violet-300">
                    Detalle
                </p>

                <template v-if="selectedLead">
                    <h3 class="mt-1 text-xl font-bold">
                        {{ selectedLead.name }}
                    </h3>

                    <p class="mt-1 text-sm text-white/50">
                        {{ formatDate(selectedLead.created_at) }}
                    </p>

                    <div class="mt-5 space-y-3 text-sm">
                        <div>
                            <p class="text-white/40">Teléfono</p>
                            <p class="text-white/80">{{ selectedLead.phone || '-' }}</p>
                        </div>

                        <div>
                            <p class="text-white/40">Correo</p>
                            <p class="text-white/80">{{ selectedLead.email || '-' }}</p>
                        </div>

                        <div>
                            <p class="text-white/40">Ciudad</p>
                            <p class="text-white/80">{{ selectedLead.city || '-' }}</p>
                        </div>

                        <div>
                            <p class="text-white/40">Tipo de proyecto</p>
                            <p class="text-white/80">{{ projectTypeLabel(selectedLead.project_type) }}</p>
                        </div>

                        <div>
                            <p class="text-white/40">Contacto preferido</p>
                            <p class="text-white/80">{{ contactMethodLabel(selectedLead.preferred_contact_method) }}</p>
                        </div>

                        <div>
                            <p class="text-white/40">Mensaje</p>
                            <p class="mt-1 rounded-2xl bg-black/20 p-4 text-white/70">
                                {{ selectedLead.message || 'Sin mensaje.' }}
                            </p>
                        </div>
                    </div>
                </template>

                <div v-else class="mt-5 rounded-2xl border border-dashed border-white/10 p-8 text-center text-white/40">
                    Selecciona una solicitud.
                </div>
            </aside>
        </div>

        <div v-if="drawerOpen" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/60" @click="drawerOpen = false"></div>

            <div class="absolute right-0 top-0 h-full w-full max-w-lg overflow-y-auto border-l border-white/10 bg-[#15111D] p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-violet-300">
                            Lead
                        </p>

                        <h2 class="text-2xl font-bold">
                            Cambiar estatus
                        </h2>
                    </div>

                    <button class="h-10 w-10 rounded-full bg-white/10 text-xl" @click="drawerOpen = false">
                        ×
                    </button>
                </div>

                <form class="mt-6 space-y-5" @submit.prevent="submit">
                    <div>
                        <label class="label">Estatus</label>
                        <select v-model="form.status" class="input">
                            <option value="new">Nueva</option>
                            <option value="contacted">Contactada</option>
                            <option value="quoted">Cotizada</option>
                            <option value="won">Ganada</option>
                            <option value="lost">Perdida</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Mensaje / nota</label>
                        <textarea v-model="form.message" class="input min-h-32"></textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-violet-500 py-3 font-semibold text-white hover:bg-violet-600 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
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

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});

const statuses = [
    { value: 'all', label: 'Todos' },
    { value: 'new', label: 'Nuevos' },
    { value: 'contacted', label: 'Contactados' },
    { value: 'quoted', label: 'Cotizados' },
    { value: 'won', label: 'Ganados' },
    { value: 'lost', label: 'Perdidos' },
];

const activeStatus = ref('all');
const selectedLead = ref(props.items?.[0] || null);
const drawerOpen = ref(false);
const editingItem = ref(null);

const form = useForm({
    status: 'new',
    message: '',
});

const filteredItems = computed(() => {
    if (activeStatus.value === 'all') return props.items;

    return props.items.filter((item) => item.status === activeStatus.value);
});

function openEdit(item) {
    editingItem.value = item;
    form.clearErrors();
    form.status = item.status || 'new';
    form.message = item.message || '';
    drawerOpen.value = true;
}

function submit() {
    if (!editingItem.value) return;

    form.post(`/admin/builder/leads/${editingItem.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            drawerOpen.value = false;
        },
    });
}

function destroy(item) {
    if (!confirm(`¿Eliminar la solicitud de "${item.name}"?`)) return;

    router.delete(`/admin/builder/leads/${item.id}`, {
        preserveScroll: true,
    });
}

function statusLabel(status) {
    const labels = {
        new: 'Nueva',
        contacted: 'Contactada',
        quoted: 'Cotizada',
        won: 'Ganada',
        lost: 'Perdida',
    };

    return labels[status] || status;
}

function statusClass(status) {
    const classes = {
        new: 'bg-violet-500/20 text-violet-200',
        contacted: 'bg-sky-500/20 text-sky-200',
        quoted: 'bg-amber-500/20 text-amber-200',
        won: 'bg-emerald-500/20 text-emerald-200',
        lost: 'bg-red-500/20 text-red-200',
    };

    return classes[status] || 'bg-white/10 text-white/70';
}

function projectTypeLabel(type) {
    const labels = {
        kitchen: 'Cocina',
        bathroom: 'Baño',
        residential: 'Residencial',
        commercial: 'Comercial',
        exterior: 'Exterior',
        other: 'Otro',
    };

    return labels[type] || 'Sin especificar';
}

function contactMethodLabel(method) {
    const labels = {
        whatsapp: 'WhatsApp',
        phone: 'Llamada',
        email: 'Correo',
    };

    return labels[method] || 'Sin especificar';
}

function formatDate(date) {
    if (!date) return '-';

    return new Date(date).toLocaleString('es-MX', {
        dateStyle: 'medium',
        timeStyle: 'short',
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

.label {
    display: block;
    margin-bottom: .5rem;
    font-size: .875rem;
    font-weight: 600;
    color: rgba(255,255,255,.8);
}
</style>
