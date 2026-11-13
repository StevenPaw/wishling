<template>
    <AppDialog :open="!!wish" labelledby="reserve-title" @close="$emit('close')">
        <form v-if="wish" class="form" novalidate @submit.prevent="submit">
            <div class="dialog_header">
                <h2 id="reserve-title">„{{ wish.title }}“ reservieren</h2>
                <button type="button" class="dialog_close" aria-label="Schließen" @click="$emit('close')">×</button>
            </div>
            <p class="dialog_text">
                Andere Gäste sehen dann, dass dieser Wunsch schon vergeben ist.
                {{ ownerName || 'Der Ersteller' }} sieht die Reservierung nicht.
            </p>

            <div class="field">
                <label for="guest-name" class="field_label">Dein Name</label>
                <input
                    id="guest-name"
                    v-model="guestName"
                    class="field_input"
                    type="text"
                    autocomplete="name"
                    maxlength="100"
                    autofocus
                    required
                    :aria-invalid="error ? 'true' : 'false'"
                    :aria-describedby="error ? 'reserve-error' : undefined"
                >
            </div>
            <div class="field">
                <label for="guest-message" class="field_label">
                    Notiz <span class="field_optional">(optional)</span>
                </label>
                <textarea
                    id="guest-message"
                    v-model="message"
                    class="field_input"
                    rows="3"
                    maxlength="2000"
                    placeholder="z. B. „Besorge ich zusammen mit Tom“"
                />
            </div>
            <p v-if="error" id="reserve-error" class="field_error" role="alert">{{ error }}</p>

            <div class="form_actions">
                <button type="submit" class="button button--primary" :disabled="submitting">
                    {{ submitting ? 'Wird reserviert …' : 'Reservieren' }}
                </button>
                <button type="button" class="button button--ghost" :disabled="submitting" @click="$emit('close')">
                    Abbrechen
                </button>
            </div>
        </form>
    </AppDialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import { load, save } from '@/utils/storage';
import AppDialog from '@/components/AppDialog.vue';

const props = defineProps({
    wish: { type: Object, default: null },
    ownerName: { type: String, default: '' },
    // Wird als @submit gebunden und muss ein Promise liefern
    onSubmit: { type: Function, required: true },
});
const emit = defineEmits(['close']);

const guestName = ref('');
const message = ref('');
const error = ref('');
const submitting = ref(false);

watch(
    () => props.wish,
    (wish) => {
        if (wish) {
            guestName.value = load('wishling.guestName', '');
            message.value = '';
            error.value = '';
        }
    },
);

async function submit() {
    error.value = '';
    if (!guestName.value.trim()) {
        error.value = 'Bitte gib deinen Namen ein.';
        return;
    }
    submitting.value = true;
    try {
        await props.onSubmit({ guestName: guestName.value.trim(), message: message.value.trim() });
        save('wishling.guestName', guestName.value.trim());
        emit('close');
    } catch (e) {
        error.value = e.message;
    } finally {
        submitting.value = false;
    }
}
</script>
