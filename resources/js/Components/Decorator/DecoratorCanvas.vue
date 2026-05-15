<template>
    <div
        ref="containerRef"
        class="relative h-full w-full overflow-hidden rounded-3xl bg-white shadow-sm border border-black/5 p-3"
    >
        <div class="relative h-full w-full overflow-hidden rounded-2xl bg-stone-100 flex items-center justify-center">
            <v-stage
                v-if="stageReady"
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

                    <v-image
                        v-if="lightImage"
                        :config="{
                            image: lightImage,
                            x: 0,
                            y: 0,
                            width: canvasWidth,
                            height: canvasHeight,
                            opacity: 0.35
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
                    <div class="rounded-2xl bg-white/95 border border-black/5 shadow-lg px-5 py-4 flex items-center gap-3">
                        <span class="h-5 w-5 rounded-full border-2 border-[#9B6A3F]/30 border-t-[#9B6A3F] animate-spin"></span>

                        <div>
                            <p class="text-sm font-semibold text-[#1F1A17]">
                                Aplicando material
                            </p>
                            <p class="text-xs text-[#6B5E55]">
                                Ajustando textura y capas...
                            </p>
                        </div>
                    </div>
                </div>
            </transition>

            <div class="absolute left-3 bottom-3 rounded-xl bg-white/90 backdrop-blur px-3 py-2 text-xs text-[#6B5E55] shadow-sm">
                <span class="font-semibold text-[#1F1A17]">
                    {{ environment.name }}
                </span>

                <span v-if="selectedZone">
                    · {{ selectedZone.name }}
                </span>
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

async function composeTextureWithMask(textureUrl, maskUrl, width, height, scale = 1, opacity = 1, rotation = 0) {
    const texture = await loadImage(textureUrl);
    const mask = await loadImage(maskUrl);

    if (!texture || !mask) return null;

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');

    const safeScale = Number(scale || 1);
    const safeOpacity = Number(opacity || 1);
    const safeRotation = Number(rotation || 0);

    const patternCanvas = document.createElement('canvas');
    patternCanvas.width = Math.max(1, texture.width * safeScale);
    patternCanvas.height = Math.max(1, texture.height * safeScale);

    const patternCtx = patternCanvas.getContext('2d');

    if (safeRotation !== 0) {
        patternCtx.save();
        patternCtx.translate(patternCanvas.width / 2, patternCanvas.height / 2);
        patternCtx.rotate((safeRotation * Math.PI) / 180);
        patternCtx.drawImage(
            texture,
            -patternCanvas.width / 2,
            -patternCanvas.height / 2,
            patternCanvas.width,
            patternCanvas.height
        );
        patternCtx.restore();
    } else {
        patternCtx.drawImage(texture, 0, 0, patternCanvas.width, patternCanvas.height);
    }

    const pattern = ctx.createPattern(patternCanvas, 'repeat');

    ctx.save();
    ctx.globalAlpha = safeOpacity;
    ctx.fillStyle = pattern;
    ctx.fillRect(0, 0, width, height);
    ctx.restore();

    ctx.globalCompositeOperation = 'destination-in';
    ctx.drawImage(mask, 0, 0, width, height);

    const output = new Image();

    return new Promise((resolve) => {
        output.onload = () => resolve(output);
        output.src = canvas.toDataURL('image/png');
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

        const image = await composeTextureWithMask(
            selection.material.texture_url,
            zone.mask_image_url,
            canvasWidth.value,
            canvasHeight.value,
            selection.scale || zone.default_texture_scale || 1,
            selection.opacity || zone.default_opacity || 1,
            selection.rotation || zone.default_texture_rotation || 0
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

watch(
    () => props.selectedMaterials,
    async () => {
        await renderZonesWithTransition();
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
