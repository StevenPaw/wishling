<template>
    <AppDialog :open="open" labelledby="confirm-title" @close="$emit('close')">
        <div class="form">
            <div class="dialog_header">
                <h2 id="confirm-title">{{ title }}</h2>
                <button type="button" class="dialog_close" aria-label="Schließen" @click="$emit('close')">×</button>
            </div>
            <p class="dialog_text">{{ text }}</p>
            <div class="form_actions">
                <button type="button" class="button button--danger" :disabled="busy" autofocus @click="confirm">
                    {{ busy ? 'Einen Moment …' : confirmLabel }}
                </button>
                <button type="button" class="button button--ghost" :disabled="busy" @click="$emit('close')">
                    Abbrechen
                </button>
            </div>
        </div>
    </AppDialog>
</template>

<script setup>
import { ref } from 'vue';
import AppDialog from '@/components/AppDialog.vue';

/**
 * Einfache Sicherheitsabfrage. Schließt sich nach erfolgreicher Bestätigung selbst.
 */
const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, required: true },
    text: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Bestätigen' },
    // Wird als @confirm gebunden und muss ein Promise liefern
    onConfirm: { type: Function, required: true },
});
const emit = defineEmits(['close']);

const busy = ref(false);

async function confirm() {
    busy.value = true;
    try {
        await props.onConfirm();
        emit('close');
    } catch {
        // Fehlermeldung kommt vom Parent
    } finally {
        busy.value = false;
    }
}
</script>
