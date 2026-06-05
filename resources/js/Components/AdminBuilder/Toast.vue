<template>
    <Teleport to="body">
        <div class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none">
            <TransitionGroup name="toast">
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    class="pointer-events-auto flex items-start gap-3 rounded-2xl border px-4 py-3 shadow-2xl min-w-[260px] max-w-[380px] backdrop-blur-sm"
                    :class="styles[t.type]"
                >
                    <span class="text-base shrink-0 mt-0.5">{{ icons[t.type] }}</span>
                    <div class="min-w-0 flex-1">
                        <p v-if="t.title" class="text-sm font-semibold leading-tight">{{ t.title }}</p>
                        <p class="text-xs opacity-80 mt-0.5 leading-snug">{{ t.message }}</p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 opacity-50 hover:opacity-100 transition text-sm"
                        @click="remove(t.id)"
                    >✕</button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { ref } from 'vue';

const toasts = ref([]);
let nextId = 0;

const styles = {
    success: 'bg-[#0D1F18]/90 border-emerald-500/40 text-emerald-100',
    error:   'bg-[#1F0D0D]/90 border-red-500/40   text-red-100',
    warning: 'bg-[#1F1A0D]/90 border-amber-500/40  text-amber-100',
    info:    'bg-[#0D0F1F]/90 border-violet-500/40 text-violet-100',
};

const icons = {
    success: '✓',
    error:   '✕',
    warning: '⚠',
    info:    'ℹ',
};

function add(type, message, title = '', duration = 4000) {
    const id = ++nextId;
    toasts.value.push({ id, type, message, title });
    if (duration > 0) {
        setTimeout(() => remove(id), duration);
    }
    return id;
}

function remove(id) {
    const idx = toasts.value.findIndex(t => t.id === id);
    if (idx !== -1) toasts.value.splice(idx, 1);
}

// Expose convenience methods
defineExpose({
    success: (msg, title) => add('success', msg, title),
    error:   (msg, title) => add('error',   msg, title, 6000),
    warning: (msg, title) => add('warning', msg, title),
    info:    (msg, title) => add('info',    msg, title),
});
</script>

<style scoped>
.toast-enter-active { transition: all 300ms cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 220ms ease; }
.toast-enter-from   { opacity: 0; transform: translateX(100%) scale(0.9); }
.toast-leave-to     { opacity: 0; transform: translateX(40px) scale(0.95); }
</style>
