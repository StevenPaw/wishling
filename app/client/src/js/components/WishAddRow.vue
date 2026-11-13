<template>
    <form class="items_row items_row--add" role="row" novalidate @submit.prevent="submit">
        <div class="items_cell items_cell--priority" role="cell" data-label="Stärke">
            <WishPriority v-model="draft.priority" label="neuer Wunsch" />
        </div>
        <div class="items_cell" role="cell" data-label="Neuer Wunsch">
            <input
                ref="titleInput"
                v-model="draft.title"
                class="items_input"
                type="text"
                maxlength="255"
                aria-label="Neuer Wunsch: Titel"
                placeholder="Neuer Wunsch"
                required
            >
        </div>
        <div class="items_cell" role="cell" data-label="Typ">
            <input
                v-model="draft.type"
                class="items_input"
                type="text"
                maxlength="100"
                aria-label="Typ (optional)"
                placeholder="Typ (optional)"
                list="wish-type-options"
                autocomplete="off"
            >
        </div>
        <div class="items_cell" role="cell" data-label="Beschreibung">
            <input
                v-model="draft.description"
                class="items_input"
                type="text"
                maxlength="5000"
                aria-label="Beschreibung (optional)"
                placeholder="Beschreibung (optional)"
            >
        </div>
        <div class="items_cell" role="cell" data-label="Preis">
            <input
                v-model="draft.price"
                class="items_input"
                type="text"
                maxlength="100"
                aria-label="Preis (optional)"
                placeholder="Preis (optional)"
            >
        </div>
        <div class="items_cell" role="cell" data-label="Link">
            <input
                v-model="draft.url"
                class="items_input"
                type="url"
                inputmode="url"
                maxlength="2048"
                aria-label="Link (optional)"
                placeholder="Link (optional)"
            >
        </div>
        <div class="items_cell items_cell--actions" role="cell">
            <button
                type="submit"
                class="button button--icon-add button--icon-only"
                aria-label="Wunsch hinzufügen"
                title="Wunsch hinzufügen (Enter)"
            />
        </div>
    </form>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useToasts } from '@/composables/useToasts';
import WishPriority from '@/components/WishPriority.vue';

const props = defineProps({
    // Wird als @add gebunden und muss ein Promise liefern
    onAdd: { type: Function, required: true },
});

const toast = useToasts();
const titleInput = ref(null);
const EMPTY = Object.freeze({ title: '', type: '', priority: 0, description: '', price: '', url: '' });
const draft = reactive({ ...EMPTY });

async function submit() {
    if (!draft.title.trim()) {
        toast.error('Bitte gib einen Titel für den Wunsch ein.');
        titleInput.value?.focus();
        return;
    }
    // Formular sofort leeren, damit man direkt den nächsten Wunsch tippen kann
    const data = { ...draft };
    Object.assign(draft, EMPTY);
    titleInput.value?.focus();

    try {
        await props.onAdd(data);
    } catch {
        // Meldung kommt vom Parent. Eingaben zurückholen, sofern nicht schon weitergetippt wurde
        if (Object.keys(EMPTY).every((key) => draft[key] === EMPTY[key])) {
            Object.assign(draft, data);
        }
    }
}
</script>
