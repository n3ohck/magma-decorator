<template>
    <div>
        <label class="block text-sm font-semibold text-white/80 mb-2">
            {{ label }}
        </label>

        <div class="rounded-3xl border border-dashed border-white/15 bg-white/5 p-4">
            <div v-if="preview" class="relative overflow-hidden rounded-2xl bg-black/20">
                <img
                    :src="preview"
                    class="w-full max-h-64 object-contain"
                    :alt="label"
                />

                <button
                    type="button"
                    class="absolute top-3 right-3 rounded-full bg-black/70 text-white h-9 w-9"
                    @click="remove"
                >
                    ×
                </button>
            </div>

            <label
                v-else
                class="flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-2xl bg-black/20 hover:bg-black/30 transition"
            >
                <input
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleChange"
                />

                <span class="text-3xl">🖼️</span>
                <span class="mt-2 text-sm text-white/70">
                    Seleccionar imagen
                </span>
                <span class="mt-1 text-xs text-white/40">
                    PNG, JPG o WEBP
                </span>
            </label>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    label: {
        type: String,
        default: 'Imagen',
    },
    modelValue: {
        type: [File, String, null],
        default: null,
    },
    currentUrl: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'remove']);

const preview = ref(props.currentUrl || null);

// Sync when currentUrl changes (switching between different items in the drawer)
watch(
    () => props.currentUrl,
    (value) => {
        if (!props.modelValue) {
            preview.value = value || null;
        }
    }
);

// Restore saved image preview when modelValue is cleared (e.g. form.reset() after
// clicking × and reopening the same item — currentUrl won't change so the watch
// above won't fire, but this one will).
watch(
    () => props.modelValue,
    (value) => {
        if (!value) {
            preview.value = props.currentUrl || null;
        }
    }
);

function handleChange(event) {
    const file = event.target.files?.[0];

    if (!file) return;

    emit('update:modelValue', file);

    const reader = new FileReader();
    reader.onload = (e) => {
        preview.value = e.target.result;
    };
    reader.readAsDataURL(file);
}

function remove() {
    preview.value = null;
    emit('update:modelValue', null);
    emit('remove');
}
</script>
