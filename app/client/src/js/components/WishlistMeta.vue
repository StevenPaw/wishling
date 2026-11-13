<template>
    <div class="wishlist_meta_block">
        <form v-if="editing" class="wishlist_meta_form form" novalidate @submit.prevent="save" @keydown.esc="cancel">
            <div class="field">
                <label for="list-title" class="field_label">Name der Liste</label>
                <input
                    id="list-title"
                    ref="titleInput"
                    v-model="draft.title"
                    class="field_input field_input--large"
                    type="text"
                    maxlength="255"
                    required
                >
            </div>
            <div class="field">
                <label for="list-description" class="field_label">
                    Beschreibung <span class="field_optional">(optional)</span>
                </label>
                <textarea
                    id="list-description"
                    v-model="draft.description"
                    class="field_input"
                    rows="3"
                    maxlength="5000"
                    @keydown.enter.ctrl.prevent="save"
                    @keydown.enter.meta.prevent="save"
                />
            </div>
            <div class="form_actions">
                <button type="submit" class="button button--primary" :disabled="saving">
                    {{ saving ? 'Wird gespeichert …' : 'Speichern' }}
                </button>
                <button type="button" class="button button--ghost" :disabled="saving" @click="cancel">Abbrechen</button>
            </div>
        </form>

        <div v-else class="wishlist_meta_view">
            <div class="wishlist_meta_text">
                <h1 class="wishlist_title">
                    {{ wishlist.title }}
                    <span v-if="wishlist.ownerName" class="wishlist_title_owner">
                        von
                        <UserAvatar v-if="wishlist.ownerAvatarUrl" :url="wishlist.ownerAvatarUrl" :name="wishlist.ownerName" size="inline" />
                        {{ wishlist.ownerName }}
                    </span>
                </h1>
                <p v-if="wishlist.description" class="wishlist_description">{{ wishlist.description }}</p>
            </div>
            <button type="button" class="button button--icon-edit" @click="startEdit">Bearbeiten</button>
        </div>
    </div>
</template>

<script setup>
import { nextTick, reactive, ref } from 'vue';
import UserAvatar from '@/components/UserAvatar.vue';

const props = defineProps({
    wishlist: { type: Object, required: true },
    saving: { type: Boolean, default: false },
    // Wird als @save gebunden, muss ein Promise liefern (bei Fehler bleibt das Formular offen)
    onSave: { type: Function, required: true },
});

const editing = ref(false);
const titleInput = ref(null);
const draft = reactive({ title: '', description: '' });

async function startEdit() {
    draft.title = props.wishlist.title;
    draft.description = props.wishlist.description;
    editing.value = true;
    await nextTick();
    titleInput.value?.focus();
}

function cancel() {
    editing.value = false;
}

async function save() {
    try {
        await props.onSave({ ...draft });
        editing.value = false;
    } catch {
        // Fehler wird vom Parent als Meldung angezeigt, Formular bleibt offen
    }
}
</script>
