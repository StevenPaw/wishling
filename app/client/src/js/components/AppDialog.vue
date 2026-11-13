<template>
    <dialog
        ref="dialog"
        class="dialog"
        :aria-labelledby="labelledby"
        @close="$emit('close')"
        @click="onBackdropClick"
    >
        <div v-if="open" class="dialog_content">
            <slot />
        </div>
    </dialog>
</template>

<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';

/**
 * Modal auf Basis des nativen <dialog>-Elements.
 * Fokus-Falle, Esc zum Schließen und Hintergrund liefert der Browser mit.
 * Das erste Feld mit "autofocus" im Inhalt bekommt beim Öffnen den Fokus.
 */
const props = defineProps({
    open: { type: Boolean, default: false },
    labelledby: { type: String, required: true },
});
defineEmits(['close']);

const dialog = ref(null);

async function sync(open) {
    if (!dialog.value) {
        return;
    }
    if (open && !dialog.value.open) {
        await nextTick();
        dialog.value.showModal();
    } else if (!open && dialog.value.open) {
        dialog.value.close();
    }
}

watch(() => props.open, sync);
onMounted(() => sync(props.open));

function onBackdropClick(event) {
    // Klick auf den abgedunkelten Hintergrund schließt den Dialog
    if (event.target === dialog.value) {
        dialog.value.close();
    }
}
</script>
