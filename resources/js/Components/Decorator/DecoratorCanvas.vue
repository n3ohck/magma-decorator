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

                <!-- Foreground: objetos que van encima -->
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

const emit = defineEmits(['applying-change']);

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

const canvasWidth = computed(() => Number(props.environment.canvas_width || 1200));
const canvasHeight = computed(() => Number(props.environment.canvas_height || 1200));

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

async function composeTextureWithMask(textureUrl, maskUrl, baseImg, width, height, scale = 1, opacity = 1, rotation = 0, perspectivePoints = null, tileOffsetX = 0, tileOffsetY = 0, microRotation = 0) {
    const texture = await loadImage(textureUrl);
    const mask = await loadImage(maskUrl);

    if (!texture || !mask) return null;

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');

    const safeScale = Number(scale || 1);
    const safeOpacity = Number(opacity || 1);
    const totalRotation = Number(rotation || 0) + Number(microRotation || 0);

    // Build the tiled pattern at the requested scale (flat — no rotation in the tile itself)
    const patternCanvas = document.createElement('canvas');
    patternCanvas.width = Math.max(1, texture.width * safeScale);
    patternCanvas.height = Math.max(1, texture.height * safeScale);

    const patternCtx = patternCanvas.getContext('2d');
    patternCtx.drawImage(texture, 0, 0, patternCanvas.width, patternCanvas.height);

    // Rotation and offset are applied together via setTransform so the entire tiling
    // grid rotates as a unit — tiles stay seamless regardless of angle.
    const offsetX = tileOffsetX * patternCanvas.width;
    const offsetY = tileOffsetY * patternCanvas.height;
    const rad = (totalRotation * Math.PI) / 180;
    const cos = Math.cos(rad);
    const sin = Math.sin(rad);
    // DOMMatrix(a,b,c,d,e,f): x' = a*x + c*y + e, y' = b*x + d*y + f
    const offsetMatrix = new DOMMatrix([cos, sin, -sin, cos, offsetX, offsetY]);

    // Draw texture — flat tile or perspective-warped
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

    // Clip to zone mask with feathered edges (blur softens the hard mask boundary
    // so the material integrates into the scene instead of cutting sharply).
    ctx.filter = 'blur(2.5px)';
    ctx.globalCompositeOperation = 'destination-in';
    ctx.drawImage(mask, 0, 0, width, height);
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

    const output = new Image();

    return new Promise((resolve) => {
        output.onload = () => resolve(output);
        output.src = canvas.toDataURL('image/png');
    });
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
    shadowImage.value = await loadImage(props.environment.shadow_overlay_url);
    lightImage.value = await loadImage(props.environment.light_overlay_url);
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
            microRotation
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

        await loadBaseImages();
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
</style>
