<template>
    <div class="cropper">
        <div
            ref="viewport"
            class="cropper_viewport"
            :class="{ 'is-dragging': dragging }"
            :style="cropStyle"
            tabindex="0"
            role="group"
            aria-label="Bildausschnitt. Ziehen oder Pfeiltasten verschieben, Plus und Minus zoomen."
            aria-describedby="cropper-hint"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp"
            @pointercancel="onPointerUp"
            @wheel.prevent="onWheel"
            @keydown="onKeydown"
        >
            <img
                v-if="src"
                ref="image"
                :src="src"
                class="cropper_image"
                :class="{ 'is-ready': ready }"
                alt=""
                draggable="false"
                @load="onLoad"
                @error="onImageError"
            >
            <div class="cropper_mask" aria-hidden="true" />
        </div>

        <div class="cropper_zoom">
            <label for="cropper-zoom" class="cropper_zoom_label">Zoom</label>
            <input
                id="cropper-zoom"
                class="cropper_zoom_slider"
                type="range"
                :min="MIN_ZOOM"
                :max="MAX_ZOOM"
                step="0.01"
                :value="zoom"
                :disabled="!ready"
                @input="setZoom(Number($event.target.value))"
            >
        </div>
        <p id="cropper-hint" class="cropper_hint">
            Ziehe das Bild, um den Ausschnitt zu wählen. Zoomen geht mit dem Regler, dem Mausrad oder mit zwei Fingern.
        </p>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';

/**
 * Quadratisches Zuschneiden eines Bildes im Browser.
 *
 * Das Bild wird so im quadratischen Ausschnitt platziert, dass dieser immer vollständig
 * bedeckt ist. Über exportBlob() entsteht ein JPEG mit max. OUTPUT_MAX × OUTPUT_MAX Pixeln.
 */
const MIN_ZOOM = 1;
const MAX_ZOOM = 4;
const OUTPUT_MAX = 800;
const KEY_STEP = 10;

const props = defineProps({
    file: { type: File, required: true },
});
const emit = defineEmits(['error']);

const viewport = ref(null);
const image = ref(null);
const src = ref('');
const ready = ref(false);
const dragging = ref(false);

// Natürliche Bildgröße, Kantenlänge des Ausschnitts (px), Zoom und Bildposition (px)
const natural = reactive({ width: 0, height: 0 });
const viewportSize = ref(0);
const zoom = ref(1);
const x = ref(0);
const y = ref(0);

// Skalierung, bei der das Bild den Ausschnitt gerade vollständig bedeckt
const baseScale = computed(() => {
    const shortSide = Math.min(natural.width, natural.height);
    return shortSide ? viewportSize.value / shortSide : 1;
});
const scale = computed(() => baseScale.value * zoom.value);

// Position und Skalierung gehen als CSS-Variablen an das SCSS
const cropStyle = computed(() => ({
    '--crop-x': `${x.value}px`,
    '--crop-y': `${y.value}px`,
    '--crop-scale': scale.value,
}));

let resizeObserver = null;

watch(
    () => props.file,
    (file) => {
        if (src.value) {
            URL.revokeObjectURL(src.value);
        }
        ready.value = false;
        src.value = file ? URL.createObjectURL(file) : '';
    },
    { immediate: true },
);

onMounted(() => {
    resizeObserver = new ResizeObserver(() => {
        const size = viewport.value?.clientWidth ?? 0;
        if (!size || size === viewportSize.value) {
            return;
        }
        // Bild war schon geladen, bevor der Dialog sichtbar war
        if (!viewportSize.value) {
            viewportSize.value = size;
            if (natural.width) {
                center();
            }
            return;
        }
        // Bei Größenänderung den gewählten Ausschnitt beibehalten
        const ratio = viewportSize.value ? size / viewportSize.value : 1;
        x.value *= ratio;
        y.value *= ratio;
        viewportSize.value = size;
        clamp();
    });
    resizeObserver.observe(viewport.value);
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    if (src.value) {
        URL.revokeObjectURL(src.value);
    }
});

function onLoad() {
    natural.width = image.value.naturalWidth;
    natural.height = image.value.naturalHeight;
    viewportSize.value = viewport.value.clientWidth;
    if (viewportSize.value) {
        center();
    }
    viewport.value.focus({ preventScroll: true });
}

/** Startzustand: Zoom 1, Bild mittig */
function center() {
    zoom.value = 1;
    x.value = (viewportSize.value - natural.width * scale.value) / 2;
    y.value = (viewportSize.value - natural.height * scale.value) / 2;
    ready.value = true;
}

function onImageError() {
    emit('error', 'Dieses Bild kann dein Browser nicht öffnen. Bitte wähle ein JPG, PNG oder WebP.');
}

/** Hält das Bild so, dass der Ausschnitt immer vollständig bedeckt ist */
function clamp() {
    const width = natural.width * scale.value;
    const height = natural.height * scale.value;
    x.value = Math.min(0, Math.max(viewportSize.value - width, x.value));
    y.value = Math.min(0, Math.max(viewportSize.value - height, y.value));
}

/** Zoomt um einen Punkt im Ausschnitt (Standard: Mitte), dieser Punkt bleibt stehen */
function setZoom(value, centerX = viewportSize.value / 2, centerY = viewportSize.value / 2) {
    if (!ready.value) {
        return;
    }
    const previous = scale.value;
    zoom.value = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, value));
    const factor = scale.value / previous;
    x.value = centerX - (centerX - x.value) * factor;
    y.value = centerY - (centerY - y.value) * factor;
    clamp();
}

// Maus und Touch, inkl. Pinch-Zoom mit zwei Fingern
const pointers = new Map();

function relativePoint(event) {
    const rect = viewport.value.getBoundingClientRect();
    return { x: event.clientX - rect.left, y: event.clientY - rect.top };
}

function onPointerDown(event) {
    if (!ready.value) {
        return;
    }
    viewport.value.setPointerCapture(event.pointerId);
    pointers.set(event.pointerId, relativePoint(event));
    dragging.value = true;
}

function onPointerMove(event) {
    if (!pointers.has(event.pointerId)) {
        return;
    }
    const previous = pointers.get(event.pointerId);
    const current = relativePoint(event);

    if (pointers.size === 1) {
        x.value += current.x - previous.x;
        y.value += current.y - previous.y;
        clamp();
    } else if (pointers.size === 2) {
        const [otherId] = [...pointers.keys()].filter((id) => id !== event.pointerId);
        const other = pointers.get(otherId);
        const before = Math.hypot(previous.x - other.x, previous.y - other.y);
        const after = Math.hypot(current.x - other.x, current.y - other.y);
        if (before > 0) {
            setZoom(zoom.value * (after / before), (current.x + other.x) / 2, (current.y + other.y) / 2);
        }
    }
    pointers.set(event.pointerId, current);
}

function onPointerUp(event) {
    pointers.delete(event.pointerId);
    dragging.value = pointers.size > 0;
}

function onWheel(event) {
    const point = relativePoint(event);
    setZoom(zoom.value * (event.deltaY < 0 ? 1.1 : 1 / 1.1), point.x, point.y);
}

function onKeydown(event) {
    const moves = {
        ArrowLeft: [KEY_STEP, 0],
        ArrowRight: [-KEY_STEP, 0],
        ArrowUp: [0, KEY_STEP],
        ArrowDown: [0, -KEY_STEP],
    };
    if (moves[event.key]) {
        event.preventDefault();
        x.value += moves[event.key][0];
        y.value += moves[event.key][1];
        clamp();
    } else if (event.key === '+' || event.key === '=') {
        event.preventDefault();
        setZoom(zoom.value * 1.1);
    } else if (event.key === '-') {
        event.preventDefault();
        setZoom(zoom.value / 1.1);
    }
}

/**
 * Erzeugt das zugeschnittene, quadratische JPEG (max. 800 × 800, kein Hochskalieren)
 */
function exportBlob() {
    if (!ready.value) {
        return Promise.reject(new Error('Das Bild ist noch nicht geladen.'));
    }
    const side = viewportSize.value / scale.value;
    const sourceX = -x.value / scale.value;
    const sourceY = -y.value / scale.value;
    const outputSize = Math.max(1, Math.min(OUTPUT_MAX, Math.round(side)));

    const canvas = document.createElement('canvas');
    canvas.width = outputSize;
    canvas.height = outputSize;
    const context = canvas.getContext('2d');
    context.imageSmoothingQuality = 'high';
    context.fillStyle = '#1E1623';
    context.fillRect(0, 0, outputSize, outputSize);
    context.drawImage(image.value, sourceX, sourceY, side, side, 0, 0, outputSize, outputSize);

    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => (blob ? resolve(blob) : reject(new Error('Das Bild konnte nicht erstellt werden.'))),
            'image/jpeg',
            0.9,
        );
    });
}

defineExpose({ exportBlob, ready });
</script>
