<template>
    <div class="space-y-3">
        <!-- Toolbar -->
        <div class="flex items-center gap-2 flex-wrap">
            <button
                type="button"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition"
                :class="activeTool === 'polygon'
                    ? 'bg-violet-500 text-white'
                    : 'bg-white/10 text-white/70 hover:bg-white/20'"
                @click="activeTool = 'polygon'"
            >
                ✦ Polígono
            </button>

            <button
                type="button"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition"
                :class="activeTool === 'sam'
                    ? 'bg-violet-500 text-white'
                    : 'bg-white/10 text-white/70 hover:bg-white/20'"
                @click="activeTool = 'sam'"
            >
                🤖 SAM (IA)
            </button>

            <div class="flex-1" />

            <button
                v-if="points.length > 0"
                type="button"
                class="px-3 py-1.5 text-xs font-semibold bg-white/10 text-white/70 hover:bg-white/20 rounded-lg"
                @click="undoLastPoint"
            >
                ↩ Deshacer
            </button>

            <button
                v-if="points.length >= 3 && !maskReady"
                type="button"
                class="px-3 py-1.5 text-xs font-semibold bg-emerald-500/80 hover:bg-emerald-500 text-white rounded-lg"
                @click="closeAndFill"
            >
                ✓ Cerrar forma
            </button>

            <button
                type="button"
                class="px-3 py-1.5 text-xs font-semibold bg-white/10 text-white/70 hover:bg-red-500/60 hover:text-white rounded-lg"
                @click="reset"
            >
                ✕ Limpiar
            </button>
        </div>

        <!-- Canvas container -->
        <div
            class="relative overflow-hidden rounded-xl select-none"
            :class="activeTool === 'polygon' && !maskReady ? 'cursor-crosshair' : 'cursor-default'"
        >
            <!-- Base image -->
            <img
                ref="imgRef"
                :src="imageUrl"
                class="w-full block"
                draggable="false"
                @load="onImageLoad"
            />

            <!-- SAM overlay (mode SAM) -->
            <img
                v-if="activeTool === 'sam' && samMaskDataUrl"
                :src="samMaskDataUrl"
                class="absolute inset-0 w-full h-full object-cover opacity-60 mix-blend-screen pointer-events-none"
            />

            <!-- Drawing canvas overlay -->
            <canvas
                ref="canvasRef"
                class="absolute inset-0 w-full h-full"
                :style="{ pointerEvents: activeTool === 'polygon' && !maskReady ? 'auto' : 'none' }"
                @click="onCanvasClick"
                @mousemove="onMouseMove"
            />

            <!-- SAM click overlay -->
            <div
                v-if="activeTool === 'sam'"
                class="absolute inset-0"
                :class="samLoading ? 'pointer-events-none' : 'cursor-crosshair'"
                @click="onSamClick"
            >
                <!-- SAM click indicator -->
                <div
                    v-if="samClickPos"
                    class="absolute h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-violet-400 bg-violet-500/30"
                    :style="{ left: samClickPos.x * 100 + '%', top: samClickPos.y * 100 + '%' }"
                />
            </div>

            <!-- SAM loading -->
            <div v-if="samLoading" class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-xl">
                <div class="flex items-center gap-3 bg-[#1a1a2e] border border-violet-500/30 rounded-2xl px-5 py-4">
                    <span class="h-5 w-5 border-2 border-violet-400/30 border-t-violet-400 rounded-full animate-spin" />
                    <p class="text-sm font-semibold text-violet-300">SAM 2 procesando…</p>
                </div>
            </div>

            <!-- Error -->
            <div v-if="samError" class="absolute bottom-2 left-2 right-2 bg-red-500/80 text-white text-xs px-3 py-2 rounded-lg">
                {{ samError }}
            </div>
        </div>

        <!-- Instructions -->
        <p v-if="activeTool === 'polygon' && !maskReady && points.length === 0" class="text-xs text-white/40">
            Haz clic en la imagen para colocar puntos. Al tener 3 o más, puedes cerrar la forma.
        </p>
        <p v-else-if="activeTool === 'polygon' && points.length > 0 && !maskReady" class="text-xs text-white/40">
            {{ points.length }} punto(s) colocado(s). Clic para agregar más o "Cerrar forma" para completar.
        </p>
        <p v-else-if="activeTool === 'sam'" class="text-xs text-white/40">
            Haz clic en el área que quieres seleccionar. SAM 2 detectará automáticamente los bordes.
        </p>

        <!-- Save button -->
        <button
            v-if="maskReady || samMaskDataUrl"
            type="button"
            class="w-full py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2"
            :class="uploadState === 'done'
                ? 'bg-emerald-500 text-white cursor-default'
                : 'bg-violet-500 hover:bg-violet-400 text-white'"
            :disabled="uploadState === 'uploading'"
            @click="saveMask"
        >
            <span v-if="uploadState === 'uploading'" class="h-4 w-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
            {{ uploadState === 'uploading' ? 'Guardando…' : uploadState === 'done' ? '✓ Máscara guardada' : 'Usar esta máscara' }}
        </button>

        <!-- Upload error -->
        <div v-if="uploadState === 'error'" class="rounded-lg bg-red-500/20 border border-red-500/30 px-3 py-2 text-xs text-red-300">
            {{ uploadError }}
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { nextTick, ref, watch } from 'vue';

const props = defineProps({
    imageUrl:       { type: String, required: true },
    naturalWidth:   { type: Number, default: 0 },
    naturalHeight:  { type: Number, default: 0 },
});

const emit = defineEmits(['mask-ready']); // { mask_path, mask_url, data_url }

// ── State ─────────────────────────────────────────────────────────────────────

const activeTool    = ref('polygon');
const imgRef        = ref(null);
const canvasRef     = ref(null);

// Points stored as FRACTIONS (0-1) of the displayed canvas — immune to layout
// changes, DPR differences, and CSS/pixel width mismatches.
const points        = ref([]);  // [{ fx: 0-1, fy: 0-1 }]
const mouseFrac     = ref(null);
const maskReady     = ref(false);

// SAM
const samLoading     = ref(false);
const samError       = ref('');
const samClickPos    = ref(null);
const samMaskDataUrl = ref(null);

// Upload state
const uploadState = ref('idle'); // idle | uploading | done | error
const uploadError = ref('');

// ── Canvas setup ──────────────────────────────────────────────────────────────

function onImageLoad() {
    nextTick(syncCanvasSize);
}

// Keep canvas pixel resolution matching its CSS rendered size so that
// the canvas coordinate space (0..width, 0..height) matches CSS pixels exactly.
function syncCanvasSize() {
    if (!canvasRef.value) return;
    const r = canvasRef.value.getBoundingClientRect();
    if (!r.width || !r.height) return;
    canvasRef.value.width  = Math.round(r.width);
    canvasRef.value.height = Math.round(r.height);
    redraw();
}

watch(activeTool, () => {
    points.value    = [];
    maskReady.value = false;
    redraw();
});

// ── Coordinate helpers ────────────────────────────────────────────────────────

// Convert a DOM event to fractions relative to the canvas element
function eventToFrac(e, el) {
    const r = el.getBoundingClientRect();
    return {
        fx: Math.max(0, Math.min(1, (e.clientX - r.left)  / r.width)),
        fy: Math.max(0, Math.min(1, (e.clientY - r.top)   / r.height)),
    };
}

// Convert fractions to canvas pixel coords (using current canvas dimensions)
function fracToPx({ fx, fy }) {
    const c = canvasRef.value;
    return { x: fx * (c?.width ?? 1), y: fy * (c?.height ?? 1) };
}

// Convert fractions to natural image coords
function fracToNat({ fx, fy }) {
    const natW = props.naturalWidth  || imgRef.value?.naturalWidth  || 1;
    const natH = props.naturalHeight || imgRef.value?.naturalHeight || 1;
    return { x: fx * natW, y: fy * natH };
}

// ── Drawing ───────────────────────────────────────────────────────────────────

function onCanvasClick(e) {
    if (maskReady.value) return;
    syncCanvasSize(); // ensure canvas dimensions are current
    points.value.push(eventToFrac(e, canvasRef.value));
    redraw();
}

function onMouseMove(e) {
    if (maskReady.value || points.value.length === 0) {
        mouseFrac.value = null;
        return;
    }
    mouseFrac.value = eventToFrac(e, canvasRef.value);
    redraw();
}

function redraw() {
    const canvas = canvasRef.value;
    if (!canvas || !canvas.width) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (points.value.length === 0) return;

    const px = points.value.map(fracToPx);
    const mp = mouseFrac.value ? fracToPx(mouseFrac.value) : null;

    ctx.strokeStyle = '#a78bfa';
    ctx.fillStyle   = 'rgba(139, 92, 246, 0.25)';
    ctx.lineWidth   = 2;
    ctx.setLineDash([6, 3]);

    ctx.beginPath();
    ctx.moveTo(px[0].x, px[0].y);
    px.slice(1).forEach(p => ctx.lineTo(p.x, p.y));

    if (mp && !maskReady.value) ctx.lineTo(mp.x, mp.y);

    if (maskReady.value) {
        ctx.closePath();
        ctx.setLineDash([]);
        ctx.strokeStyle = '#10b981';
        ctx.fillStyle   = 'rgba(16, 185, 129, 0.25)';
        ctx.fill();
    }

    ctx.stroke();

    ctx.setLineDash([]);
    px.forEach((p, i) => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, i === 0 ? 6 : 4, 0, Math.PI * 2);
        ctx.fillStyle   = i === 0 ? '#10b981' : '#a78bfa';
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth   = 1;
        ctx.stroke();
    });
}

function undoLastPoint() {
    points.value.pop();
    maskReady.value = false;
    redraw();
}

function closeAndFill() {
    if (points.value.length < 3) return;
    maskReady.value  = true;
    mouseFrac.value  = null;
    redraw();
}

function reset() {
    points.value         = [];
    maskReady.value      = false;
    mouseFrac.value      = null;
    samMaskDataUrl.value = null;
    samClickPos.value    = null;
    samError.value       = '';
    uploadState.value    = 'idle';
    uploadError.value    = '';
    const canvas = canvasRef.value;
    if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
}

// ── Export polygon mask ───────────────────────────────────────────────────────

async function saveMask() {
    if (activeTool.value === 'polygon') await savePolygonMask();
    else if (samMaskDataUrl.value)       await saveSamMask();
}

async function savePolygonMask() {
    if (points.value.length < 3) return;

    // Step 1: draw polygon in DISPLAY coords (same as redraw — guaranteed correct)
    const c  = canvasRef.value;
    const dw = c.width;
    const dh = c.height;

    const tmp    = document.createElement('canvas');
    tmp.width    = dw;
    tmp.height   = dh;
    const tmpCtx = tmp.getContext('2d');

    const px = points.value.map(fracToPx);
    tmpCtx.beginPath();
    tmpCtx.moveTo(px[0].x, px[0].y);
    px.slice(1).forEach(p => tmpCtx.lineTo(p.x, p.y));
    tmpCtx.closePath();
    tmpCtx.fillStyle = 'rgba(255,255,255,1)';
    tmpCtx.fill();

    // Step 2: scale up to natural (canvas_width × canvas_height).
    // drawImage handles the proportional scaling; no manual math needed.
    const natW = props.naturalWidth  || imgRef.value?.naturalWidth  || dw;
    const natH = props.naturalHeight || imgRef.value?.naturalHeight || dh;

    const mc  = document.createElement('canvas');
    mc.width  = natW;
    mc.height = natH;
    mc.getContext('2d').drawImage(tmp, 0, 0, natW, natH);

    await uploadAndEmit(mc.toDataURL('image/png'));
}

async function saveSamMask() {
    await uploadAndEmit(samMaskDataUrl.value);
}

async function uploadAndEmit(dataUrl) {
    uploadState.value = 'uploading';
    uploadError.value = '';

    try {
        // Convert data URL to a File object — the parent form will upload it
        // via the normal mask_image file field, avoiding sam_mask_path edge-cases.
        const file = dataUrlToFile(dataUrl, 'mask.png');

        uploadState.value = 'done';
        emit('mask-ready', {
            file,
            preview_url: dataUrl,
        });
    } catch (e) {
        uploadState.value = 'error';
        uploadError.value = 'No se pudo procesar la máscara: ' + (e?.message ?? 'error desconocido');
        console.error('MaskEditor error', e);
    }
}

function dataUrlToFile(dataUrl, filename) {
    const [header, b64] = dataUrl.split(',');
    const mime   = header.match(/:(.*?);/)[1];
    const binary = atob(b64);
    const bytes  = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
    return new File([bytes], filename, { type: mime });
}

// ── SAM tool ──────────────────────────────────────────────────────────────────

async function onSamClick(e) {
    if (samLoading.value) return;

    const r   = e.currentTarget.getBoundingClientRect();
    const fracX = (e.clientX - r.left)  / r.width;
    const fracY = (e.clientY - r.top)   / r.height;

    samClickPos.value    = { x: fracX, y: fracY };
    samMaskDataUrl.value = null;
    samError.value       = '';
    samLoading.value     = true;

    try {
        // Fetch image as base64 so Replicate can access it without public URL
        const imgData = await fetchAsBase64(props.imageUrl);
        const natW    = props.naturalWidth  || imgRef.value?.naturalWidth  || 1200;
        const natH    = props.naturalHeight || imgRef.value?.naturalHeight || 1200;

        const { data } = await axios.post('/admin/builder/sam-mask', {
            image_data:   imgData,
            x:            fracX,
            y:            fracY,
            image_width:  natW,
            image_height: natH,
        });

        samMaskDataUrl.value = data.mask_url || data.mask_data_url;
    } catch (err) {
        samError.value = err?.response?.data?.error ?? 'SAM no pudo generar la máscara.';
    } finally {
        samLoading.value = false;
    }
}

async function fetchAsBase64(url) {
    const res    = await fetch(url);
    const blob   = await res.blob();
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.readAsDataURL(blob);
    });
}
</script>
