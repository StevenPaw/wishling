<template>
    <AppDialog :open="dialog.state.open" labelledby="wishlist-dialog-title" @close="dialog.close()">
        <form class="form" novalidate @submit.prevent="submit">
            <div class="dialog_header">
                <h2 id="wishlist-dialog-title">{{ source ? 'Wunschliste duplizieren' : 'Neue Wunschliste' }}</h2>
                <button type="button" class="dialog_close" aria-label="Schließen" @click="dialog.close()">×</button>
            </div>

            <p v-if="source" class="dialog_text">
                {{ copyHint }}
            </p>

            <div class="field">
                <label for="wishlist-dialog-name" class="field_label">Name der Liste</label>
                <input
                    id="wishlist-dialog-name"
                    v-model="title"
                    class="field_input"
                    type="text"
                    maxlength="255"
                    placeholder="z. B. Geburtstag 2027"
                    autofocus
                    required
                    :aria-invalid="error ? 'true' : 'false'"
                    :aria-describedby="error ? 'wishlist-dialog-error' : undefined"
                >
            </div>
            <div class="field">
                <label for="wishlist-dialog-description" class="field_label">
                    Beschreibung <span class="field_optional">(optional)</span>
                </label>
                <textarea
                    id="wishlist-dialog-description"
                    v-model="description"
                    class="field_input"
                    rows="3"
                    maxlength="5000"
                    @keydown.enter.ctrl.prevent="submit"
                    @keydown.enter.meta.prevent="submit"
                />
            </div>
            <p v-if="error" id="wishlist-dialog-error" class="field_error" role="alert">{{ error }}</p>

            <div class="form_actions">
                <button type="submit" class="button button--primary" :disabled="saving">
                    {{ submitLabel }}
                </button>
                <button type="button" class="button button--ghost" :disabled="saving" @click="dialog.close()">
                    Abbrechen
                </button>
            </div>
        </form>
    </AppDialog>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { wishlistApi } from '@/api';
import { useToasts } from '@/composables/useToasts';
import { useWishlistDialog } from '@/composables/useWishlistDialog';
import AppDialog from '@/components/AppDialog.vue';

const router = useRouter();
const toast = useToasts();
const dialog = useWishlistDialog();

const title = ref('');
const description = ref('');
const error = ref('');
const saving = ref(false);

const source = computed(() => dialog.state.source);

const copyHint = computed(() => {
    const count = source.value?.wishCount ?? 0;
    const wishes = count === 1 ? 'Der aktive Wunsch wird' : `Alle ${count} aktiven Wünsche werden`;
    return `${wishes} aus „${source.value?.title}“ übernommen – ohne Reservierungen.`;
});

const submitLabel = computed(() => {
    if (saving.value) {
        return source.value ? 'Wird dupliziert …' : 'Wird angelegt …';
    }
    return source.value ? 'Duplizieren' : 'Wunschliste anlegen';
});

watch(
    () => dialog.state.open,
    (open) => {
        if (!open) {
            return;
        }
        error.value = '';
        title.value = source.value ? `${source.value.title} (Kopie)` : '';
        description.value = source.value?.description ?? '';
    },
);

async function submit() {
    error.value = '';
    if (!title.value.trim()) {
        error.value = 'Bitte gib einen Namen für die Liste ein.';
        return;
    }

    saving.value = true;
    try {
        const data = { title: title.value, description: description.value };
        const { wishlist } = source.value
            ? await wishlistApi.duplicate(source.value.hash, data)
            : await wishlistApi.create(data);
        dialog.close();
        toast.success(source.value ? `„${wishlist.title}“ wurde angelegt.` : 'Deine Wunschliste ist angelegt.');
        router.push({ name: 'wishlist-edit', params: { hash: wishlist.hash } });
    } catch (e) {
        error.value = e.message;
    } finally {
        saving.value = false;
    }
}
</script>
