<template>
    <section class="guest_gifts" aria-labelledby="guest-gifts-title">
        <h2 id="guest-gifts-title" class="guest_gifts_title">Weitere Geschenke</h2>
        <p class="guest_gifts_hint">
            Du schenkst etwas, das nicht auf der Liste steht? Trag es hier ein, damit niemand dasselbe besorgt.
            {{ ownerName || 'Der Ersteller' }} sieht diese Einträge nicht.
        </p>

        <ul v-if="gifts.length" class="guest_gifts_list">
            <li v-for="gift in gifts" :key="gift.hash" class="guest_gift">
                <div class="guest_gift_text">
                    <span class="guest_gift_title">{{ gift.title }}</span>
                    <span class="guest_gift_meta">von {{ gift.guestName }} · {{ formatDate(gift.created) }}</span>
                </div>
                <button
                    type="button"
                    class="button button--ghost button--small"
                    :aria-label="`„${gift.title}“ von ${gift.guestName} entfernen`"
                    @click="$emit('remove', gift)"
                >
                    Entfernen
                </button>
            </li>
        </ul>

        <form class="guest_gifts_form" novalidate @submit.prevent="submit">
            <div class="field guest_gifts_field--title">
                <label for="gift-title" class="field_label">Was schenkst du?</label>
                <input
                    id="gift-title"
                    v-model="title"
                    class="field_input"
                    type="text"
                    maxlength="255"
                    placeholder="z. B. Kinogutschein"
                    required
                    @focus="prefillName"
                >
            </div>
            <div class="field guest_gifts_field--name">
                <label for="gift-name" class="field_label">Dein Name</label>
                <input
                    id="gift-name"
                    v-model="guestName"
                    class="field_input"
                    type="text"
                    autocomplete="name"
                    maxlength="100"
                    required
                >
            </div>
            <button type="submit" class="button button--primary" :disabled="saving">
                {{ saving ? 'Wird eingetragen …' : 'Eintragen' }}
            </button>
        </form>
        <p v-if="error" class="field_error" role="alert">{{ error }}</p>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { formatDate } from '@/utils/format';
import { loadGuestName, saveGuestName } from '@/utils/guestName';

const props = defineProps({
    gifts: { type: Array, required: true },
    ownerName: { type: String, default: '' },
    // Wird als @add gebunden und muss ein Promise liefern
    onAdd: { type: Function, required: true },
});
defineEmits(['remove']);

const title = ref('');
const guestName = ref(loadGuestName());
const saving = ref(false);
const error = ref('');

// Den gemerkten Namen nachladen, z. B. wenn der Gast inzwischen etwas reserviert hat
function prefillName() {
    if (!guestName.value.trim()) {
        guestName.value = loadGuestName();
    }
}

async function submit() {
    error.value = '';
    prefillName();
    if (!title.value.trim()) {
        error.value = 'Bitte gib ein, was du schenkst.';
        return;
    }
    if (!guestName.value.trim()) {
        error.value = 'Bitte gib deinen Namen ein.';
        return;
    }
    saving.value = true;
    try {
        await props.onAdd({ title: title.value.trim(), guestName: guestName.value.trim() });
        saveGuestName(guestName.value);
        title.value = '';
    } catch (e) {
        error.value = e.message;
    } finally {
        saving.value = false;
    }
}
</script>
