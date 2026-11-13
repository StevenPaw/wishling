<template>
    <div class="editable_cell" :class="{ 'is-editing': editing, 'is-saving': saving }">
        <template v-if="editing">
            <textarea
                v-if="multiline"
                ref="input"
                v-model="draft"
                class="items_input"
                rows="3"
                :maxlength="maxlength"
                :aria-label="`${label} (Enter speichert, Umschalt+Enter für neue Zeile, Esc bricht ab)`"
                :placeholder="placeholder"
                @keydown="onKeydown"
                @blur="commit(false)"
            />
            <input
                v-else
                ref="input"
                v-model="draft"
                class="items_input"
                :type="inputType"
                :inputmode="inputType === 'url' ? 'url' : undefined"
                :maxlength="maxlength"
                :list="list || undefined"
                :aria-label="`${label} (Enter speichert, Esc bricht ab)`"
                :placeholder="placeholder"
                autocomplete="off"
                @keydown="onKeydown"
                @blur="commit(false)"
            >
        </template>

        <button
            v-else
            ref="trigger"
            type="button"
            class="editable_trigger"
            :class="{ 'is-empty': !value }"
            @click="start"
        >
            <template v-if="value">
                <slot :value="value">{{ value }}</slot>
                <span class="sr-only">, {{ label }} bearbeiten</span>
            </template>
            <span v-else class="editable_placeholder">+ {{ label }}</span>
        </button>
    </div>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import { useToasts } from '@/composables/useToasts';

/**
 * Zelle, die per Klick (oder Enter/Leertaste) zum Eingabefeld wird.
 * Speichert bei Enter oder beim Verlassen des Feldes, Esc bricht ab.
 */
const props = defineProps({
    value: { type: String, default: '' },
    // Feldname für Beschriftung und Platzhalter, z. B. "Typ"
    label: { type: String, required: true },
    multiline: { type: Boolean, default: false },
    inputType: { type: String, default: 'text' },
    maxlength: { type: Number, default: 255 },
    placeholder: { type: String, default: '' },
    // ID einer <datalist> mit Vorschlägen
    list: { type: String, default: '' },
    required: { type: Boolean, default: false },
    // Wird als @save gebunden und muss ein Promise liefern (Fehler: Feld bleibt offen)
    onSave: { type: Function, required: true },
});

const toast = useToasts();

const editing = ref(false);
const saving = ref(false);
const draft = ref('');
const input = ref(null);
const trigger = ref(null);
let cancelled = false;

async function start() {
    draft.value = props.value;
    cancelled = false;
    editing.value = true;
    await nextTick();
    input.value?.focus();
    input.value?.select();
}

async function finish(restoreFocus) {
    editing.value = false;
    if (restoreFocus) {
        // Für Tastaturbedienung: Fokus zurück auf die Zelle
        await nextTick();
        trigger.value?.focus();
    }
}

async function commit(viaKeyboard) {
    if (!editing.value || saving.value || cancelled) {
        return;
    }
    const next = draft.value.trim();

    if (next === props.value) {
        finish(viaKeyboard);
        return;
    }
    if (props.required && !next) {
        toast.error(`${props.label} darf nicht leer sein.`);
        finish(viaKeyboard);
        return;
    }

    saving.value = true;
    try {
        await props.onSave(next);
        finish(viaKeyboard);
    } catch {
        // Fehlermeldung kommt vom Parent; Feld offen lassen, damit man korrigieren kann
        await nextTick();
        input.value?.focus();
    } finally {
        saving.value = false;
    }
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        event.preventDefault();
        cancelled = true;
        finish(true);
    } else if (event.key === 'Enter' && !(props.multiline && event.shiftKey)) {
        event.preventDefault();
        commit(true);
    }
}
</script>
