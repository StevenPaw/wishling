<template>
    <AppDialog :open="!!wish" labelledby="release-title" @close="$emit('close')">
        <form v-if="wish" class="form" novalidate @submit.prevent="submit">
            <div class="dialog_header">
                <h2 id="release-title">Reservierung freigeben?</h2>
                <button type="button" class="dialog_close" aria-label="Schließen" @click="$emit('close')">×</button>
            </div>
            <p class="dialog_text">
                „{{ wish.title }}“ ist reserviert von <strong>{{ wish.reservation?.guestName }}</strong>
                <template v-if="wish.reservation?.created"> seit {{ formatDateTime(wish.reservation.created) }}</template>.
                Nach dem Freigeben kann jemand anderes den Wunsch reservieren. Die Freigabe wird mit deinem Namen
                im Verlauf vermerkt.
            </p>

            <div class="field">
                <label for="release-name" class="field_label">Dein Name</label>
                <input
                    id="release-name"
                    v-model="guestName"
                    class="field_input"
                    type="text"
                    autocomplete="name"
                    maxlength="100"
                    autofocus
                    required
                    :aria-invalid="error ? 'true' : 'false'"
                    :aria-describedby="error ? 'release-error' : undefined"
                >
            </div>
            <p v-if="error" id="release-error" class="field_error" role="alert">{{ error }}</p>

            <div class="form_actions">
                <button type="submit" class="button button--danger" :disabled="submitting">
                    {{ submitting ? 'Wird freigegeben …' : 'Ja, freigeben' }}
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
import { formatDateTime } from '@/utils/format';
import { loadGuestName, saveGuestName } from '@/utils/guestName';
import AppDialog from '@/components/AppDialog.vue';

const props = defineProps({
    wish: { type: Object, default: null },
    // Wird als @submit gebunden und muss ein Promise liefern
    onSubmit: { type: Function, required: true },
});
const emit = defineEmits(['close']);

const guestName = ref('');
const error = ref('');
const submitting = ref(false);

watch(
    () => props.wish,
    (wish) => {
        if (wish) {
            guestName.value = loadGuestName();
            error.value = '';
        }
    },
);

async function submit() {
    error.value = '';
    if (!guestName.value.trim()) {
        error.value = 'Bitte gib deinen Namen ein, damit im Verlauf steht, wer freigegeben hat.';
        return;
    }
    submitting.value = true;
    try {
        await props.onSubmit({ guestName: guestName.value.trim() });
        saveGuestName(guestName.value);
        emit('close');
    } catch (e) {
        error.value = e.message;
    } finally {
        submitting.value = false;
    }
}
</script>
