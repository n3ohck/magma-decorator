<template>
    <div
        ref="containerRef"
        class="relative h-full w-full overflow-hidden rounded-3xl bg-white shadow-sm border border-black/5 p-3"
    >
        <div class="relative h-full w-full overflow-hidden rounded-2xl bg-stone-100">
            <div
                class="absolute inset-0 flex items-center justify-center"
            >
                <v-stage
                    v-if="displayWidth > 0 && displayHeight > 0"
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

                    <!-- Zonas renderizadas -->
                    <v-layer>
                        <v-image
                            v-for="zone in renderedZoneImages"
                            :key="zone.id"
                            :config="zone.config"
                        />
                    </v-layer>

                    <!-- Overlays -->
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
                </v-stage>
            </div>

            <div class="absolute left-3 bottom-3 rounded-xl bg-white/90 backdrop-blur px-3 py-2 text-xs text-[#6B5E55] shadow-sm">
                <span class="font-semibold text-[#1F1A17]">{{ environment.name }}</span>
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

const containerRef = ref(null);
const containerWidth = ref(0);
const containerHeight = ref(0);

let resizeObserver = null;

const canvasWidth = computed(() => Number(props.environment.canvas_width || 1600));
const canvasHeight = computed(() => Number(props.environment.canvas_height || 1000));

const visualScale = computed(() => {
    if (!containerWidth.value || !containerHeight.value) {
        return 0.4;
    }

    const availableWidth = Math.max(1, containerWidth.value - 24);
    const availableHeight = Math.max(1, containerHeight.value - 24);

    const scaleX = availableWidth / canvasWidth.value;
    const scaleY = availableHeight / canvasHeight.value;

    return Math.min(scaleX, scaleY);
});

const displayWidth = computed(() => {
    return Math.floor(canvasWidth.value * visualScale.value);
});

const displayHeight = computed(() => {
    return Math.floor(canvasHeight.value * visualScale.value);
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
const renderedZoneImages = ref([]);

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
}

async function renderZones() {
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
                opacity: Number(selection.opacity || zone.default_opacity || 1),
            },
        });
    }

    renderedZoneImages.value = output;
}

function updateContainerSize() {
    if (!containerRef.value) return;

    const rect = containerRef.value.getBoundingClientRect();

    containerWidth.value = rect.width;
    containerHeight.value = rect.height;
}

watch(
    () => props.selectedMaterials,
    async () => {
        await renderZones();
    },
    { deep: true }
);

watch(
    () => props.environment,
    async () => {
        await loadBaseImages();
        await renderZones();

        await nextTick();
        updateContainerSize();

        setTimeout(updateContainerSize, 80);
    },
    { deep: true }
);

onMounted(async () => {
    await loadBaseImages();
    await renderZones();

    await nextTick();
    updateContainerSize();

    setTimeout(updateContainerSize, 80);
    setTimeout(updateContainerSize, 250);

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
