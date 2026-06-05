<template>
    <div class="space-y-2">
        <!-- Tags existentes -->
        <div v-if="tags.length > 0" class="flex flex-wrap gap-1.5">
            <span
                v-for="(tag, i) in tags"
                :key="i"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-violet-500/20 text-violet-200 text-xs font-semibold border border-violet-500/30"
            >
                {{ tag }}
                <button
                    type="button"
                    class="hover:text-white transition ml-0.5 opacity-70 hover:opacity-100"
                    @click="remove(i)"
                >
                    ×
                </button>
            </span>
        </div>

        <!-- Input -->
        <div class="flex gap-2">
            <input
                ref="inputRef"
                v-model="draft"
                type="text"
                :placeholder="placeholder"
                class="flex-1 h-9 border border-white/12 bg-white/[0.05] px-3 text-sm text-white placeholder:text-white/25 outline-none focus:border-white/30 transition rounded-lg"
                @keydown.enter.prevent="add"
                @keydown.comma.prevent="add"
                @keydown.backspace="onBackspace"
                @blur="add"
            />
            <button
                v-if="draft.trim()"
                type="button"
                class="px-3 h-9 text-xs font-semibold rounded-lg bg-violet-500/80 hover:bg-violet-500 text-white transition"
                @click="add"
            >
                +
            </button>
        </div>

        <p class="text-[10px] text-white/25">
            Presiona <kbd class="px-1 py-0.5 rounded bg-white/10 text-white/40 text-[10px]">Enter</kbd> o
            <kbd class="px-1 py-0.5 rounded bg-white/10 text-white/40 text-[10px]">,</kbd> para agregar
        </p>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Agregar keyword…' },
});

const emit = defineEmits(['update:modelValue']);

const draft    = ref('');
const inputRef = ref(null);
const tags     = ref([...(props.modelValue ?? [])]);

watch(() => props.modelValue, (val) => {
    tags.value = [...(val ?? [])];
}, { deep: true });

function add() {
    const value = draft.value.trim().replace(/,+$/, '');
    if (!value) return;
    // evitar duplicados
    if (!tags.value.includes(value)) {
        tags.value.push(value);
        emit('update:modelValue', [...tags.value]);
    }
    draft.value = '';
}

function remove(index) {
    tags.value.splice(index, 1);
    emit('update:modelValue', [...tags.value]);
}

function onBackspace() {
    if (draft.value === '' && tags.value.length > 0) {
        tags.value.pop();
        emit('update:modelValue', [...tags.value]);
    }
}
</script>
