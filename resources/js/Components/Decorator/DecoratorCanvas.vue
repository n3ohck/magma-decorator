<template>
    <div
        ref="containerRef"
        class="relative h-full w-full overflow-hidden bg-[#0D0D0D] p-2"
    >
        <div class="relative h-full w-full overflow-hidden bg-[#1A1A1A] flex items-center justify-center">
            <v-stage
                v-if="stageReady"
                ref="stageRef"
                :config="stageConfig"
            >
                <!-- Imagen base -->
                <v-layer>
                    <v-image
                        v-if="baseImage"
                        :config="{
                            image: baseImage,
                            x: 0,
                            y: 0,
                            width: canvasWidth,
                            height: canvasHeight
                        }"
                    />
                </v-layer>

                <!-- Texturas aplicadas con fade -->
                <v-layer>
                    <v-image
                        v-for="zone in renderedZoneImages"
                        :key="zone.id"
                        :config="zone.config"
                    />
                </v-layer>

                <!-- Overlays de sombra/luz -->
                <v-layer>
                    <v-image
                        v-if="shadowImage"
                        :config="{
                            image: shadowImage,
                            x: 0,
                            y: 0,
                            width: canvasWidth,
                            height: canvasHeight,
                            opacity: 0.45
                        }"
                    />

                    <!-- screen: luces brillantes de la foto queman la textura como lo harían en realidad -->
                    <v-image
                        v-if="lightImage"
                        :config="{
                            image: lightImage,
                            x: 0,
                            y: 0,
                            width: canvasWidth,
                            height: canvasHeight,
                            opacity: 0.5,
                            globalCompositeOperation: 'screen'
                        }"
                    />
                </v-layer>

                <!-- Foreground: objetos que van encima + highlight de zonas del grupo -->
                <v-layer>
                    <v-image
                        v-if="foregroundImage"
                        :config="{
                            image: foregroundImage,
                            x: 0,
                            y: 0,
                            width: canvasWidth,
                            height: canvasHeight,
                            opacity: foregroundOpacity
                        }"
                    />

                    <!-- Highlight de zonas del grupo activo (sólo en hover del punto) -->
                    <v-image
                        v-if="highlightImage"
                        :config="{
                            image: highlightImage,
                            x: 0,
                            y: 0,
                            width: canvasWidth,
                            height: canvasHeight,
                            opacity: highlightOpacityValue,
                            listening: false,
                        }"
                    />
                </v-layer>

                <!-- Grain: ruido procedural para romper la limpieza digital -->
                <v-layer>
                    <v-image
                        v-if="grainImage"
                        :config="{
                            image: grainImage,
                            x: 0,
                            y: 0,
                            width: canvasWidth,
                            height: canvasHeight,
                            opacity: 0.045,
                            globalCompositeOperation: 'overlay'
                        }"
                    />
                </v-layer>
            </v-stage>

            <div
                v-if="!stageReady"
                class="text-sm text-[#6B5E55]"
            >
                Cargando ambiente...
            </div>

            <!-- Loading elegante -->
            <transition name="canvas-fade">
                <div
                    v-if="isApplying"
                    class="absolute inset-0 z-20 flex items-center justify-center bg-white/35 backdrop-blur-[2px]"
                >
                    <div class="bg-black/80 border border-white/10 px-5 py-4 flex items-center gap-3">
                        <span class="h-4 w-4 border-2 border-white/20 border-t-[#CC1A1A] animate-spin shrink-0"></span>
                        <p class="text-xs font-semibold text-white uppercase tracking-[0.15em]">
                            Aplicando material…
                        </p>
                    </div>
                </div>
            </transition>

            <!-- Zone group dots overlay -->
            <div
                v-if="stageReady"
                class="absolute pointer-events-none"
                :style="{
                    width:  displayWidth  + 'px',
                    height: displayHeight + 'px',
                }"
            >
                <template v-for="group in activeGroupsWithLabel" :key="group.id">
                    <div
                        class="absolute pointer-events-auto"
                        :style="{
                            left:      (group.label_x * 100) + '%',
                            top:       (group.label_y * 100) + '%',
                            transform: 'translate(-50%, -50%)',
                        }"
                        @mouseenter="hoveredGroup = group.id"
                        @mouseleave="hoveredGroup = null"
                    >
                        <!-- Pulse ring -->
                        <span
                            class="absolute inset-0 rounded-full animate-ping opacity-60"
                            :style="{ backgroundColor: group.color || '#CC1A1A' }"
                        />

                        <!-- Dot button -->
                        <button
                            type="button"
                            class="relative flex h-7 w-7 items-center justify-center rounded-full border-2 border-white font-bold text-white shadow-lg transition-transform hover:scale-110 text-sm leading-none cursor-pointer"
                            :style="{ backgroundColor: group.color || '#CC1A1A' }"
                            :aria-label="`Seleccionar ${group.name}`"
                            @click="emit('select-group', group)"
                        >
                            +
                        </button>

                        <!-- Tooltip -->
                        <transition name="tip-fade">
                            <div
                                v-if="hoveredGroup === group.id"
                                class="absolute bottom-full left-1/2 mb-2 -translate-x-1/2 pointer-events-none z-30"
                            >
                                <div
                                    class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold text-white shadow-xl"
                                    :style="{ backgroundColor: group.color || '#CC1A1A' }"
                                >
                                    {{ group.name }}
                                </div>
                                <!-- Arrow -->
                                <div
                                    class="mx-auto mt-0.5 h-0 w-0"
                                    :style="{
                                        borderLeft: '5px solid transparent',
                                        borderRight: '5px solid transparent',
                                        borderTop: `5px solid ${group.color || '#CC1A1A'}`,
                                        width: 'fit-content',
                                    }"
                                />
                            </div>
                        </transition>
                    </div>
                </template>
            </div>

            <div class="absolute left-3 bottom-3 bg-black/70 backdrop-blur px-3 py-1.5 text-[10px] text-white/50 uppercase tracking-[0.15em]">
                <span class="font-semibold text-white/80">{{ environment.name }}</span>
                <span v-if="selectedZone"> · {{ selectedZone.name }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    environment: {
        type: Object,
        required: true,
    },
    selectedZone: {
        type: Object,
        default: null,
    },
    selectedMaterials: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['applying-change', 'select-group']);

const stageRef = ref(null);

/**
 * Captures the current canvas as a PNG data URL at 2× resolution.
 * Called by the parent when the user requests an AI render.
 */
function captureImage() {
    return stageRef.value?.getStage()?.toDataURL({ mimeType: 'image/png', pixelRatio: 2 }) ?? null;
}

defineExpose({ captureImage });

const containerRef = ref(null);
const containerWidth = ref(0);
const containerHeight = ref(0);

let resizeObserver = null;
let renderToken = 0;

const isApplying = ref(false);
const foregroundOpacity = ref(1);
const hoveredGroup = ref(null);

const activeGroupsWithLabel = computed(() => {
    return (props.environment.active_zone_groups || []).filter(
        (g) => g.label_x != null && g.label_y != null,
    );
});

// Effective canvas dimensions: use the base image's NATURAL dimensions once loaded,
// falling back to environment.canvas_width/height. This avoids stretching the image
// when the configured canvas dimensions don't match the actual image proportions.
const imageNaturalWidth  = ref(0);
const imageNaturalHeight = ref(0);

const canvasWidth = computed(() => {
    if (imageNaturalWidth.value  > 0) return imageNaturalWidth.value;
    return Number(props.environment.canvas_width  || 1200);
});
const canvasHeight = computed(() => {
    if (imageNaturalHeight.value > 0) return imageNaturalHeight.value;
    return Number(props.environment.canvas_height || 1200);
});

const availableWidth = computed(() => {
    return Math.max(1, containerWidth.value - 24);
});

const availableHeight = computed(() => {
    return Math.max(1, containerHeight.value - 24);
});

const visualScale = computed(() => {
    if (!availableWidth.value || !availableHeight.value) {
        return 0.5;
    }

    const scaleX = availableWidth.value / canvasWidth.value;
    const scaleY = availableHeight.value / canvasHeight.value;

    return Math.min(scaleX, scaleY);
});

const displayWidth = computed(() => {
    return Math.max(1, Math.floor(canvasWidth.value * visualScale.value));
});

const displayHeight = computed(() => {
    return Math.max(1, Math.floor(canvasHeight.value * visualScale.value));
});

const stageReady = computed(() => {
    return displayWidth.value > 1 && displayHeight.value > 1;
});

const stageConfig = computed(() => ({
    width: displayWidth.value,
    height: displayHeight.value,
    scaleX: visualScale.value,
    scaleY: visualScale.value,
}));

const baseImage = ref(null);
const shadowImage = ref(null);
const lightImage = ref(null);
const foregroundImage = ref(null);
const grainImage = ref(null);

const renderedZoneImages = ref([]);

// ── Iluminación de zonas por grupo ────────────────────────────────────────────
// Tinte suave del color del grupo, recortado a las máscaras de sus zonas.
// Se enciende al hacer hover sobre el punto o cuando el grupo está seleccionado.
const groupHighlights = ref({});   // { [groupId]: HTMLCanvasElement }

// El highlight se muestra ÚNICAMENTE al hacer hover sobre el punto del grupo.
const highlightImage = computed(() => {
    const id = hoveredGroup.value;
    if (id == null) return null;
    return groupHighlights.value[id] || null;
});

// Pulso muy sutil mientras se mantiene el hover (~12% de opacidad promedio).
const highlightPulse = ref(0.12);
let highlightRAF = null;

function startHighlightPulse() {
    if (highlightRAF) return;
    const loop = () => {
        const t = performance.now() / 1000;
        highlightPulse.value = 0.09 + 0.06 * (0.5 + 0.5 * Math.sin(t * 2.4));
        highlightRAF = requestAnimationFrame(loop);
    };
    highlightRAF = requestAnimationFrame(loop);
}

function stopHighlightPulse() {
    if (highlightRAF) {
        cancelAnimationFrame(highlightRAF);
        highlightRAF = null;
    }
}

const highlightOpacityValue = computed(() => {
    return hoveredGroup.value != null ? highlightPulse.value : 0;
});

watch(hoveredGroup, (val) => {
    if (val != null) startHighlightPulse();
    else stopHighlightPulse();
});

// Construye un canvas teñido con el color del grupo, recortado a las máscaras
// (con alpha) de todas sus zonas. Bordes suavizados para un look elegante.
// Tinte blanco para un "glow" elegante que ilumina la zona sin aportar color.
const HIGHLIGHT_COLOR = '#FFFFFF';

async function buildGroupHighlight(group) {
    const zones = group.active_zones || group.zones || [];
    const w = canvasWidth.value;
    const h = canvasHeight.value;
    const color = HIGHLIGHT_COLOR;

    const canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');

    let painted = false;

    for (const zone of zones) {
        if (!zone.mask_image_url) continue;

        const mask = await loadImage(zone.mask_image_url);
        if (!mask) continue;

        // Tiñe la forma de la máscara con el color del grupo (source-in usa el alpha).
        const tinted = document.createElement('canvas');
        tinted.width = w;
        tinted.height = h;
        const tctx = tinted.getContext('2d');
        tctx.drawImage(mask, 0, 0, w, h);
        tctx.globalCompositeOperation = 'source-in';
        tctx.fillStyle = color;
        tctx.fillRect(0, 0, w, h);

        ctx.drawImage(tinted, 0, 0);
        painted = true;
    }

    return painted ? canvas : null;
}

async function buildGroupHighlights() {
    const result = {};
    for (const group of (props.environment.active_zone_groups || [])) {
        const canvas = await buildGroupHighlight(group);
        if (canvas) result[group.id] = canvas;
    }
    groupHighlights.value = result;
}

function setApplying(value) {
    isApplying.value = value;
    emit('applying-change', value);
}

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function loadImage(url) {
    return new Promise((resolve) => {
        if (!url) {
            resolve(null);
            return;
        }

        const image = new Image();
        image.crossOrigin = 'anonymous';

        image.onload = () => resolve(image);

        image.onerror = () => {
            console.warn('No se pudo cargar la imagen:', url);
            resolve(null);
        };

        image.src = url;
    });
}

// Deterministic pseudo-random from an integer seed (0..1)
function seededRandom(seed) {
    const x = Math.sin(seed + 1) * 10000;
    return x - Math.floor(x);
}

// Bilinear interpolation over a 4-point quad [TL, TR, BR, BL] (each [x,y] or {x,y})
function bilerp(pts, u, v) {
    const px = (p) => (Array.isArray(p) ? p[0] : p.x);
    const py = (p) => (Array.isArray(p) ? p[1] : p.y);
    return {
        x: (1 - u) * (1 - v) * px(pts[0]) + u * (1 - v) * px(pts[1]) + u * v * px(pts[2]) + (1 - u) * v * px(pts[3]),
        y: (1 - u) * (1 - v) * py(pts[0]) + u * (1 - v) * py(pts[1]) + u * v * py(pts[2]) + (1 - u) * v * py(pts[3]),
    };
}

// Draws img onto ctx using affine transform that maps src triangle → dst triangle
function drawAffineTriangle(ctx, img, d0, d1, d2, s0, s1, s2) {
    const denom = (s1.x - s0.x) * (s2.y - s0.y) - (s2.x - s0.x) * (s1.y - s0.y);
    if (Math.abs(denom) < 1e-6) return;

    const a = ((d1.x - d0.x) * (s2.y - s0.y) - (d2.x - d0.x) * (s1.y - s0.y)) / denom;
    const b = ((d1.y - d0.y) * (s2.y - s0.y) - (d2.y - d0.y) * (s1.y - s0.y)) / denom;
    const c = ((d2.x - d0.x) * (s1.x - s0.x) - (d1.x - d0.x) * (s2.x - s0.x)) / denom;
    const d = ((d2.y - d0.y) * (s1.x - s0.x) - (d1.y - d0.y) * (s2.x - s0.x)) / denom;
    const e = d0.x - a * s0.x - c * s0.y;
    const f = d0.y - b * s0.x - d * s0.y;

    ctx.save();
    ctx.beginPath();
    ctx.moveTo(d0.x, d0.y);
    ctx.lineTo(d1.x, d1.y);
    ctx.lineTo(d2.x, d2.y);
    ctx.closePath();
    ctx.clip();
    ctx.transform(a, b, c, d, e, f);
    ctx.drawImage(img, 0, 0);
    ctx.restore();
}

// Warp a pre-tiled texture canvas onto a perspective quad via grid subdivision
function drawTextureWithPerspective(ctx, tiledCanvas, pts, width, height, subdivisions = 24) {
    for (let row = 0; row < subdivisions; row++) {
        for (let col = 0; col < subdivisions; col++) {
            const u0 = col / subdivisions, u1 = (col + 1) / subdivisions;
            const v0 = row / subdivisions, v1 = (row + 1) / subdivisions;

            const s00 = { x: u0 * width, y: v0 * height };
            const s10 = { x: u1 * width, y: v0 * height };
            const s01 = { x: u0 * width, y: v1 * height };
            const s11 = { x: u1 * width, y: v1 * height };

            const d00 = bilerp(pts, u0, v0);
            const d10 = bilerp(pts, u1, v0);
            const d01 = bilerp(pts, u0, v1);
            const d11 = bilerp(pts, u1, v1);

            drawAffineTriangle(ctx, tiledCanvas, d00, d10, d11, s00, s10, s11);
            drawAffineTriangle(ctx, tiledCanvas, d00, d11, d01, s00, s11, s01);
        }
    }
}

// Construye una "super-baldosa" NxN a partir de la textura para romper la sensación
// de patrón web. Cada celda se voltea (espejo H/V) de forma determinista y recibe una
// ligera variación de brillo, de modo que la repetición macro pasa a ser N× más larga
// y el ojo no reconoce la rejilla. Como la textura es tileable, los espejos preservan
// las costuras (los bordes opuestos coinciden), así que no aparecen cortes.
function buildVariedTile(texture, tileW, tileH, seed, grid = 3) {
    const cols = grid;
    const rows = grid;

    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.floor(tileW * cols));
    canvas.height = Math.max(1, Math.floor(tileH * rows));

    const ctx = canvas.getContext('2d');

    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
            const s = seed + (r * cols + c) * 97;
            const flipX = seededRandom(s) > 0.5;
            const flipY = seededRandom(s + 1) > 0.5;
            // Variación de luminosidad ±5% para que dos celdas iguales no se lean idénticas.
            const bright = (seededRandom(s + 2) - 0.5) * 0.10;

            const x = c * tileW;
            const y = r * tileH;

            ctx.save();
            ctx.translate(x + (flipX ? tileW : 0), y + (flipY ? tileH : 0));
            ctx.scale(flipX ? -1 : 1, flipY ? -1 : 1);
            ctx.drawImage(texture, 0, 0, tileW, tileH);
            ctx.restore();

            // Overlay de brillo, recortado a la celda.
            ctx.save();
            ctx.beginPath();
            ctx.rect(x, y, tileW, tileH);
            ctx.clip();
            ctx.fillStyle = bright >= 0
                ? `rgba(255,255,255,${bright})`
                : `rgba(0,0,0,${-bright})`;
            ctx.fillRect(x, y, tileW, tileH);
            ctx.restore();
        }
    }

    return canvas;
}

// Book Match "auto": una sola mariposa (4 vías) centrada y ajustada al muro completo.
// Divide el bounding-box del muro (bbox, en coordenadas del canvas) en 4 cuadrantes y
// coloca una losa por cuadrante, escalada uniformemente para cubrirlo (cover, sin
// deformar), con el eje de espejo en el centro del muro. Los bordes colindantes de las
// losas coinciden → veteado simétrico tipo "libro abierto" en ambos ejes.
//
// Como el bbox se calcula sobre la unión de las máscaras del grupo, todas las zonas del
// muro comparten el mismo eje y escala, así que juntas forman UNA mariposa continua.
function drawBookMatchAuto(ctx, texture, bbox, opacity = 1, mode = 'two') {
    const cx = (bbox.minX + bbox.maxX) / 2;
    const cy = (bbox.minY + bbox.maxY) / 2;
    const halfW = (bbox.maxX - bbox.minX) / 2;
    const halfH = (bbox.maxY - bbox.minY) / 2;

    if (halfW <= 0 || halfH <= 0) return;

    ctx.save();
    ctx.globalAlpha = opacity;

    // Traslape de 1px hacia el eje del espejo: como los lados son mirror uno del otro,
    // solaparlos 1px elimina la hairline fina en la costura sin romper la simetría.
    const bleed = 1;

    if (mode === 'four') {
        // 4 vías: mariposa/diamante. Pre-renderizamos UN cuadrante opaco (la losa cover)
        // y lo colocamos 4 veces con espejo y un pequeño traslape hacia los ejes. Al dibujar
        // imágenes completas (sin recorte por cuadrante) no queda hairline ni línea clara en
        // el eje horizontal/vertical del espejo.
        const s = Math.max(halfW / texture.width, halfH / texture.height);
        const dw = texture.width * s;
        const dh = texture.height * s;

        const qw = Math.ceil(halfW);
        const qh = Math.ceil(halfH);
        const q = document.createElement('canvas');
        q.width = qw;
        q.height = qh;
        // Losa anclada en la esquina interior del cuadrante (hacia el centro del muro).
        q.getContext('2d').drawImage(texture, qw - dw, qh - dh, dw, dh);

        const ov = Math.max(2, bleed + 1); // traslape hacia los ejes

        const placeQuad = (flipX, flipY) => {
            ctx.save();
            ctx.translate(flipX ? 2 * cx : 0, flipY ? 2 * cy : 0);
            ctx.scale(flipX ? -1 : 1, flipY ? -1 : 1);
            // Cuadrante base (sup-izq), extendido ov px pasando el eje para solapar el vecino.
            ctx.drawImage(q, cx - halfW, cy - halfH, halfW + ov, halfH + ov);
            ctx.restore();
        };

        placeQuad(false, false);
        placeQuad(true, false);
        placeQuad(false, true);
        placeQuad(true, true);
    } else {
        // 2 vías: espejo sólo izquierda↔derecha (spine vertical), losa a altura completa.
        // El veteado fluye natural de arriba a abajo, como una losa real abierta en libro.
        const fullH = halfH * 2;
        const s = Math.max(halfW / texture.width, fullH / texture.height);
        const dw = texture.width * s;
        const dh = texture.height * s;

        const half = (flipX) => {
            ctx.save();
            ctx.beginPath();
            ctx.rect(flipX ? cx - bleed : cx - halfW, cy - halfH, halfW + bleed, fullH);
            ctx.clip();
            if (flipX) { ctx.translate(2 * cx, 0); ctx.scale(-1, 1); }
            // Losa anclada: borde derecho en cx, centrada verticalmente en el muro.
            ctx.drawImage(texture, cx - dw, cy - dh / 2, dw, dh);
            ctx.restore();
        };

        half(false); // izquierda
        half(true);  // derecha (espejo H)
    }

    ctx.restore();
}

// Bounding-box (en coordenadas del canvas) del área opaca de una máscara.
// Escanea a resolución reducida para ser barato.
async function computeMaskBBox(maskUrl, width, height) {
    const mask = await loadImage(maskUrl);
    if (!mask) return null;

    const maxDim = 320;
    const ratio = Math.min(maxDim / width, maxDim / height, 1);
    const w = Math.max(1, Math.round(width * ratio));
    const h = Math.max(1, Math.round(height * ratio));

    const c = document.createElement('canvas');
    c.width = w;
    c.height = h;
    const cc = c.getContext('2d');
    cc.drawImage(mask, 0, 0, w, h);
    const data = cc.getImageData(0, 0, w, h).data;

    let minX = w, minY = h, maxX = -1, maxY = -1;
    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            if (data[(y * w + x) * 4 + 3] > 10) {
                if (x < minX) minX = x;
                if (x > maxX) maxX = x;
                if (y < minY) minY = y;
                if (y > maxY) maxY = y;
            }
        }
    }

    if (maxX < 0) return null;

    return {
        minX: minX / ratio,
        minY: minY / ratio,
        maxX: (maxX + 1) / ratio,
        maxY: (maxY + 1) / ratio,
    };
}

// Cache de bbox para book match, por grupo (eje común de muro) o por zona suelta.
let bookMatchBBoxCache = {};

// bbox a usar para el book match de una zona:
//   - Si pertenece a un grupo → unión de las máscaras de TODAS sus zonas (muro completo).
//   - Si es zona suelta → su propia máscara.
async function getBookMatchBBox(zone) {
    const group = (props.environment.active_zone_groups || []).find(
        (g) => (g.active_zones || g.zones || []).some((z) => z.id === zone.id),
    );

    const key = group ? `g${group.id}` : `z${zone.id}`;
    if (bookMatchBBoxCache[key]) return bookMatchBBoxCache[key];

    const zonesForBBox = group ? (group.active_zones || group.zones || []) : [zone];

    let union = null;
    for (const z of zonesForBBox) {
        if (!z.mask_image_url) continue;
        const bb = await computeMaskBBox(z.mask_image_url, canvasWidth.value, canvasHeight.value);
        if (!bb) continue;
        union = union
            ? {
                minX: Math.min(union.minX, bb.minX),
                minY: Math.min(union.minY, bb.minY),
                maxX: Math.max(union.maxX, bb.maxX),
                maxY: Math.max(union.maxY, bb.maxY),
            }
            : bb;
    }

    bookMatchBBoxCache[key] = union;
    return union;
}

async function composeTextureWithMask(textureUrl, maskUrl, baseImg, width, height, scale = 1, opacity = 1, rotation = 0, perspectivePoints = null, tileOffsetX = 0, tileOffsetY = 0, microRotation = 0, seed = 0, bookMatch = false, bookMatchBBox = null, bookMatchMode = 'two') {
    const texture = await loadImage(textureUrl);
    const mask = await loadImage(maskUrl);

    if (!texture || !mask) return null;

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');

    const safeScale = Number(scale || 1);
    const safeOpacity = Number(opacity || 1);

    // Draw texture — book match auto, or flat/perspective tiling.
    if (bookMatch && bookMatchBBox) {
        // Book Match: una sola mariposa 4 vías ajustada al muro completo. Ignora la escala
        // manual (auto-ajuste a 1 losa por cuadrante) y el tiling aleatorio, para lograr la
        // simetría limpia de las referencias.
        drawBookMatchAuto(ctx, texture, bookMatchBBox, safeOpacity, bookMatchMode);
    } else {
        // Tiling normal con super-baldosa 3×3 (volteos aleatorios + variación de brillo)
        // para que la repetición no se lea como un patrón web (ver buildVariedTile).
        const totalRotation = Number(rotation || 0) + Number(microRotation || 0);
        const tileW = Math.max(1, texture.width * safeScale);
        const tileH = Math.max(1, texture.height * safeScale);
        const patternCanvas = buildVariedTile(texture, tileW, tileH, seed);

        // Rotation and offset are applied together via setTransform so the entire tiling
        // grid rotates as a unit — tiles stay seamless regardless of angle.
        const offsetX = tileOffsetX * patternCanvas.width;
        const offsetY = tileOffsetY * patternCanvas.height;
        const rad = (totalRotation * Math.PI) / 180;
        const cos = Math.cos(rad);
        const sin = Math.sin(rad);
        // DOMMatrix(a,b,c,d,e,f): x' = a*x + c*y + e, y' = b*x + d*y + f
        const offsetMatrix = new DOMMatrix([cos, sin, -sin, cos, offsetX, offsetY]);

        if (perspectivePoints && perspectivePoints.length === 4) {
            // Pre-tile to full canvas size with offset applied, then warp via grid subdivision
            const tiledCanvas = document.createElement('canvas');
            tiledCanvas.width = width;
            tiledCanvas.height = height;
            const tiledCtx = tiledCanvas.getContext('2d');
            const tiledPattern = tiledCtx.createPattern(patternCanvas, 'repeat');
            tiledPattern.setTransform(offsetMatrix);
            tiledCtx.fillStyle = tiledPattern;
            tiledCtx.fillRect(0, 0, width, height);

            drawTextureWithPerspective(ctx, tiledCanvas, perspectivePoints, width, height);
        } else {
            const pattern = ctx.createPattern(patternCanvas, 'repeat');
            pattern.setTransform(offsetMatrix);

            ctx.save();
            ctx.globalAlpha = safeOpacity;
            ctx.fillStyle = pattern;
            ctx.fillRect(0, 0, width, height);
            ctx.restore();
        }
    }

    // Bake surface lighting: multiply the base image shading onto the texture.
    // This makes shadows, reflections and depth from the original photo transfer
    // to the material, so it looks physically part of the scene.
    if (baseImg) {
        ctx.globalCompositeOperation = 'multiply';
        ctx.globalAlpha = 0.55;
        ctx.drawImage(baseImg, 0, 0, width, height);
        ctx.globalAlpha = 1;
        ctx.globalCompositeOperation = 'source-over';
    }

    // Clip to zone mask. Dilatamos ligeramente la máscara (varias copias desplazadas)
    // para que zonas contiguas de un mismo muro se SOLAPEN y no dejen una costura —una
    // línea clara del fondo— entre ellas. Luego un feather suave integra el borde exterior
    // en la escena sin el corte duro tipo "sticker".
    const dilate = Math.max(1, Math.round(width * 0.0012));
    const dilatedMask = document.createElement('canvas');
    dilatedMask.width = width;
    dilatedMask.height = height;
    const dmCtx = dilatedMask.getContext('2d');
    const dmOffsets = [
        [0, 0], [dilate, 0], [-dilate, 0], [0, dilate], [0, -dilate],
        [dilate, dilate], [-dilate, -dilate], [dilate, -dilate], [-dilate, dilate],
    ];
    for (const [ox, oy] of dmOffsets) {
        dmCtx.drawImage(mask, ox, oy, width, height);
    }

    ctx.filter = 'blur(1.5px)';
    ctx.globalCompositeOperation = 'destination-in';
    ctx.drawImage(dilatedMask, 0, 0, width, height);
    ctx.filter = 'none';

    // Vignette: subtle darkening at zone edges gives depth and avoids the
    // "floating sticker" look. Uses source-atop so it only touches visible pixels.
    ctx.globalCompositeOperation = 'source-atop';
    const vignette = ctx.createRadialGradient(
        width * 0.5, height * 0.5, width * 0.08,
        width * 0.5, height * 0.5, width * 0.68
    );
    vignette.addColorStop(0, 'rgba(0,0,0,0)');
    vignette.addColorStop(1, 'rgba(0,0,0,0.22)');
    ctx.fillStyle = vignette;
    ctx.fillRect(0, 0, width, height);
    ctx.globalCompositeOperation = 'source-over';

    // Devolvemos el canvas directamente — Konva lo acepta como fuente de imagen.
    // Evita el ciclo encode PNG → decode Image que introducía una vuelta asíncrona
    // extra y podía dejar el canvas en estado inconsistente durante animaciones.
    return canvas;
}

function generateGrainImage(width, height) {
    // Generates a monochromatic noise tile. Rendered once at mount; cheap.
    const size = 512; // tile size — will be scaled by Konva to canvas dimensions
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;
    const ctx = canvas.getContext('2d');
    const imageData = ctx.createImageData(size, size);
    const data = imageData.data;

    for (let i = 0; i < data.length; i += 4) {
        const v = Math.floor(Math.random() * 256);
        data[i] = v;
        data[i + 1] = v;
        data[i + 2] = v;
        data[i + 3] = 255;
    }

    ctx.putImageData(imageData, 0, 0);

    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.src = canvas.toDataURL('image/png');
    });
}

async function loadBaseImages() {
    baseImage.value = await loadImage(props.environment.base_image_url);

    // Use actual image dimensions as the coordinate space — avoids stretching
    // when canvas_width/height don't match the uploaded image proportions.
    if (baseImage.value?.naturalWidth) {
        imageNaturalWidth.value  = baseImage.value.naturalWidth;
        imageNaturalHeight.value = baseImage.value.naturalHeight;
    }

    shadowImage.value     = await loadImage(props.environment.shadow_overlay_url);
    lightImage.value      = await loadImage(props.environment.light_overlay_url);
    foregroundImage.value = await loadImage(props.environment.foreground_overlay_url);
}

async function buildRenderedZones() {
    const zones = props.environment.zones || [];
    const output = [];

    for (const zone of zones) {
        const selection = props.selectedMaterials?.[zone.id];

        if (!selection?.material?.texture_url || !zone.mask_image_url) {
            continue;
        }

        // Deterministic offsets: same zone+material always produces the same tile origin
        const seed = zone.id * 137 + (selection.material.id ?? 0) * 251;
        const tileOffsetX = seededRandom(seed);
        const tileOffsetY = seededRandom(seed + 7);
        const microRotation = (seededRandom(seed + 13) - 0.5) * 6; // ±3°

        const bookMatch = selection.bookMatch === true;

        // Book Match: bbox del muro completo (unión del grupo) o de la zona suelta,
        // para que el eje de espejo sea común entre las zonas del muro.
        const bookMatchBBox = bookMatch ? await getBookMatchBBox(zone) : null;

        const image = await composeTextureWithMask(
            selection.material.texture_url,
            zone.mask_image_url,
            baseImage.value,
            canvasWidth.value,
            canvasHeight.value,
            selection.scale || zone.default_texture_scale || 1,
            selection.opacity || zone.default_opacity || 1,
            selection.rotation || zone.default_texture_rotation || 0,
            zone.supports_perspective && zone.perspective_points?.length === 4
                ? zone.perspective_points
                : null,
            tileOffsetX,
            tileOffsetY,
            microRotation,
            seed,
            bookMatch,
            bookMatchBBox,
            selection.bookMatchMode || 'two'
        );

        if (!image) continue;

        output.push({
            id: zone.id,
            config: {
                image,
                x: 0,
                y: 0,
                width: canvasWidth.value,
                height: canvasHeight.value,
                opacity: 0,
            },
        });
    }

    return output;
}

async function fadeRenderedZones(targetOpacity = 1, duration = 260) {
    const frames = 12;
    const stepTime = duration / frames;

    for (let i = 1; i <= frames; i++) {
        const ratio = i / frames;

        renderedZoneImages.value = renderedZoneImages.value.map((zone) => ({
            ...zone,
            config: {
                ...zone.config,
                opacity: targetOpacity * ratio,
            },
        }));

        foregroundOpacity.value = Math.min(1, 0.65 + 0.35 * ratio);

        await sleep(stepTime);
    }
}

async function fadeOutCurrentZones(duration = 180) {
    if (!renderedZoneImages.value.length) return;

    const original = renderedZoneImages.value.map((zone) => ({
        id: zone.id,
        opacity: Number(zone.config.opacity || 1),
    }));

    const frames = 8;
    const stepTime = duration / frames;

    for (let i = 1; i <= frames; i++) {
        const ratio = 1 - i / frames;

        renderedZoneImages.value = renderedZoneImages.value.map((zone) => {
            const current = original.find((item) => item.id === zone.id);
            const baseOpacity = current?.opacity ?? 1;

            return {
                ...zone,
                config: {
                    ...zone.config,
                    opacity: baseOpacity * ratio,
                },
            };
        });

        foregroundOpacity.value = Math.max(0.75, ratio);

        await sleep(stepTime);
    }
}

async function renderZonesWithTransition() {
    const token = ++renderToken;

    setApplying(true);

    try {
        await fadeOutCurrentZones();

        if (token !== renderToken) return;

        const output = await buildRenderedZones();

        if (token !== renderToken) return;

        renderedZoneImages.value = output;

        await nextTick();
        await fadeRenderedZones(1, 280);

        // Re-render final garantizado: la animación de fade puede dejar el canvas
        // en estado inconsistente si el token cambió a mitad. Este render instantáneo
        // asegura que el estado visible siempre coincide con selectedMaterials actual.
        if (token === renderToken) {
            await renderZonesInstant();
        }
    } finally {
        if (token === renderToken) {
            foregroundOpacity.value = 1;
            await sleep(80);
            setApplying(false);
        }
    }
}

async function renderZonesInstant() {
    const output = await buildRenderedZones();

    renderedZoneImages.value = output.map((zone) => ({
        ...zone,
        config: {
            ...zone.config,
            opacity: 1,
        },
    }));
}

function updateContainerSize() {
    if (!containerRef.value) return;

    const rect = containerRef.value.getBoundingClientRect();

    containerWidth.value = rect.width;
    containerHeight.value = rect.height;
}

async function refreshCanvas() {
    await nextTick();

    updateContainerSize();

    requestAnimationFrame(() => {
        updateContainerSize();
    });

    setTimeout(updateContainerSize, 100);
    setTimeout(updateContainerSize, 300);
}

function sameMaterialIds(a, b) {
    if (!a || !b) return false;
    const keysA = Object.keys(a).sort();
    const keysB = Object.keys(b).sort();
    if (keysA.join() !== keysB.join()) return false;
    return keysA.every((k) => a[k]?.material?.id === b[k]?.material?.id);
}

watch(
    () => props.selectedMaterials,
    async (newVal, oldVal) => {
        if (sameMaterialIds(oldVal, newVal)) {
            // Only scale/rotation/opacity changed — skip transition
            await renderZonesInstant();
        } else {
            await renderZonesWithTransition();
        }
    },
    { deep: true }
);

watch(
    () => props.environment,
    async () => {
        setApplying(true);

        bookMatchBBoxCache = {};
        await loadBaseImages();
        await buildGroupHighlights();
        await renderZonesInstant();
        await refreshCanvas();

        setApplying(false);
    },
    { deep: true }
);

onMounted(async () => {
    setApplying(true);

    // Generate grain once — no need to regenerate on environment change
    grainImage.value = await generateGrainImage();

    await loadBaseImages();
    await buildGroupHighlights();
    await renderZonesInstant();
    await refreshCanvas();

    setApplying(false);

    if (containerRef.value) {
        resizeObserver = new ResizeObserver(() => {
            updateContainerSize();
        });

        resizeObserver.observe(containerRef.value);
    }

    window.addEventListener('resize', updateContainerSize);
});

onBeforeUnmount(() => {
    if (resizeObserver) {
        resizeObserver.disconnect();
    }

    stopHighlightPulse();
    window.removeEventListener('resize', updateContainerSize);
});
</script>

<style scoped>
.canvas-fade-enter-active,
.canvas-fade-leave-active {
    transition: opacity 220ms ease;
}

.canvas-fade-enter-from,
.canvas-fade-leave-to {
    opacity: 0;
}

.tip-fade-enter-active,
.tip-fade-leave-active {
    transition: opacity 120ms ease, transform 120ms ease;
}

.tip-fade-enter-from,
.tip-fade-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(4px);
}
</style>
