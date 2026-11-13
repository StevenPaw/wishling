<template>
    <section class="section--Auth">
        <div class="section_content">
            <div class="auth_card">
                <template v-if="welcome">
                    <h1>Willkommen bei Wishling!</h1>
                    <p class="auth_hint">Wie sollen dich andere auf deinen Wunschlisten sehen?</p>
                </template>
                <h1 v-else>Dein Profil</h1>

                <div class="profile_avatar">
                    <UserAvatar :url="auth.state.user?.avatarUrl || ''" :name="displayName || auth.state.user?.email || ''" size="large" />
                    <div class="profile_avatar_actions">
                        <span class="field_label">Profilbild</span>
                        <div class="form_actions">
                            <button type="button" class="button button--small" :disabled="avatarBusy" @click="fileInput.click()">
                                {{ auth.state.user?.avatarUrl ? 'Bild ändern' : 'Bild hochladen' }}
                            </button>
                            <button
                                v-if="auth.state.user?.avatarUrl"
                                type="button"
                                class="button button--ghost button--small"
                                :disabled="avatarBusy"
                                @click="removeAvatar"
                            >
                                Entfernen
                            </button>
                        </div>
                        <input
                            ref="fileInput"
                            class="sr-only"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            tabindex="-1"
                            aria-hidden="true"
                            @change="onFileSelected"
                        >
                    </div>
                </div>

                <form class="form" novalidate @submit.prevent="submit">
                    <div class="field">
                        <label for="profile-name" class="field_label">Dein Name</label>
                        <input
                            id="profile-name"
                            ref="nameInput"
                            v-model="displayName"
                            class="field_input"
                            type="text"
                            autocomplete="name"
                            maxlength="100"
                            required
                            :aria-invalid="error ? 'true' : 'false'"
                            :aria-describedby="error ? 'profile-error' : 'profile-name-hint'"
                        >
                        <p id="profile-name-hint" class="field_hint">Wird Gästen zusammen mit deinem Profilbild an deinen Wunschlisten angezeigt.</p>
                        <p v-if="error" id="profile-error" class="field_error" role="alert">{{ error }}</p>
                    </div>

                    <div class="field">
                        <span class="field_label">E-Mail-Adresse</span>
                        <p class="field_static">{{ auth.state.user?.email }}</p>
                    </div>

                    <button type="submit" class="button button--primary button--block" :disabled="saving">
                        {{ saving ? 'Wird gespeichert …' : (welcome ? 'Weiter' : 'Speichern') }}
                    </button>
                </form>

                <div v-if="!welcome" class="auth_footer">
                    <button type="button" class="button button--ghost" @click="logout">Abmelden</button>
                </div>
            </div>
        </div>

        <AppDialog :open="!!cropFile" labelledby="avatar-dialog-title" @close="cropFile = null">
            <div class="form">
                <div class="dialog_header">
                    <h2 id="avatar-dialog-title">Profilbild zuschneiden</h2>
                    <button type="button" class="dialog_close" aria-label="Schließen" @click="cropFile = null">×</button>
                </div>
                <AvatarCropper v-if="cropFile" ref="cropper" :file="cropFile" @error="onCropError" />
                <p v-if="avatarError" class="field_error" role="alert">{{ avatarError }}</p>
                <div class="form_actions">
                    <button type="button" class="button button--primary" :disabled="avatarBusy" @click="saveAvatar">
                        {{ avatarBusy ? 'Wird gespeichert …' : 'Übernehmen' }}
                    </button>
                    <button type="button" class="button button--ghost" :disabled="avatarBusy" @click="cropFile = null">
                        Abbrechen
                    </button>
                </div>
            </div>
        </AppDialog>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { authApi } from '@/api';
import { useAuth } from '@/stores/auth';
import { useToasts } from '@/composables/useToasts';
import AppDialog from '@/components/AppDialog.vue';
import AvatarCropper from '@/components/AvatarCropper.vue';
import UserAvatar from '@/components/UserAvatar.vue';

const route = useRoute();
const router = useRouter();
const auth = useAuth();
const toast = useToasts();

const welcome = computed(() => route.query.willkommen === '1');
const displayName = ref(auth.state.user?.displayName || '');
const nameInput = ref(null);
const saving = ref(false);
const error = ref('');

// Profilbild
const MAX_FILE_BYTES = 20 * 1024 * 1024;
const fileInput = ref(null);
const cropper = ref(null);
const cropFile = ref(null);
const avatarBusy = ref(false);
const avatarError = ref('');

function onFileSelected(event) {
    const [file] = event.target.files;
    // Zurücksetzen, damit dieselbe Datei erneut gewählt werden kann
    event.target.value = '';
    if (!file) {
        return;
    }
    if (!file.type.startsWith('image/')) {
        toast.error('Bitte wähle eine Bilddatei aus.');
        return;
    }
    if (file.size > MAX_FILE_BYTES) {
        toast.error('Das Bild ist zu groß (maximal 20 MB).');
        return;
    }
    avatarError.value = '';
    cropFile.value = file;
}

function onCropError(message) {
    cropFile.value = null;
    toast.error(message);
}

async function saveAvatar() {
    avatarError.value = '';
    avatarBusy.value = true;
    try {
        const blob = await cropper.value.exportBlob();
        const { user } = await authApi.uploadAvatar(blob);
        auth.setUser(user);
        cropFile.value = null;
        toast.success('Profilbild gespeichert.');
    } catch (e) {
        avatarError.value = e.message;
    } finally {
        avatarBusy.value = false;
    }
}

async function removeAvatar() {
    avatarBusy.value = true;
    try {
        const { user } = await authApi.deleteAvatar();
        auth.setUser(user);
        toast.success('Profilbild entfernt.');
    } catch (e) {
        toast.error(e.message);
    } finally {
        avatarBusy.value = false;
    }
}

onMounted(() => {
    if (welcome.value) {
        nameInput.value?.focus();
    }
});

async function submit() {
    error.value = '';
    if (!displayName.value.trim()) {
        error.value = 'Bitte gib einen Namen ein.';
        return;
    }

    saving.value = true;
    try {
        const { user } = await authApi.updateMe({ displayName: displayName.value });
        auth.setUser(user);
        if (welcome.value) {
            const redirect = typeof route.query.weiter === 'string' && route.query.weiter.startsWith('/') && !route.query.weiter.startsWith('//')
                ? route.query.weiter
                : '/meine-listen';
            router.replace(redirect);
        } else {
            toast.success('Profil gespeichert.');
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        saving.value = false;
    }
}

async function logout() {
    await auth.logout();
    toast.success('Du bist jetzt abgemeldet.');
    router.push({ name: 'home' });
}
</script>
