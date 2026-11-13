<template>
    <section class="section--Auth">
        <div class="section_content">
            <div class="auth_card">
                <template v-if="sentTo">
                    <h1>Schau in dein Postfach</h1>
                    <p>
                        Wir haben dir einen Login-Link an <strong>{{ sentTo }}</strong> geschickt.
                        Er ist 15 Minuten gültig und funktioniert nur einmal.
                    </p>
                    <p class="auth_hint">Keine Mail bekommen? Schau auch im Spam-Ordner nach.</p>
                    <button type="button" class="button" @click="reset">Andere Adresse verwenden</button>
                </template>

                <template v-else>
                    <h1>Anmelden oder registrieren</h1>
                    <p class="auth_hint">
                        Gib deine E-Mail-Adresse ein. Wir schicken dir einen Link, mit dem du dich ohne Passwort anmeldest.
                        Wenn du neu bist, wird dein Account dabei automatisch angelegt.
                    </p>
                    <form class="form" novalidate @submit.prevent="submit">
                        <div class="field">
                            <label for="login-email" class="field_label">E-Mail-Adresse</label>
                            <input
                                id="login-email"
                                ref="emailInput"
                                v-model="email"
                                class="field_input"
                                type="email"
                                inputmode="email"
                                autocomplete="email"
                                autocapitalize="off"
                                spellcheck="false"
                                required
                                :aria-invalid="error ? 'true' : 'false'"
                                :aria-describedby="error ? 'login-error' : undefined"
                            >
                            <p v-if="error" id="login-error" class="field_error" role="alert">{{ error }}</p>
                        </div>
                        <button type="submit" class="button button--primary button--block" :disabled="sending">
                            {{ sending ? 'Wird gesendet …' : 'Login-Link senden' }}
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </section>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { authApi } from '@/api';
import { load, save } from '@/utils/storage';

const route = useRoute();

const email = ref(load('wishling.lastEmail', ''));
const emailInput = ref(null);
const sending = ref(false);
const sentTo = ref('');
const error = ref('');

onMounted(() => emailInput.value?.focus());

async function submit() {
    error.value = '';
    const value = email.value.trim();
    if (!value || !value.includes('@')) {
        error.value = 'Bitte gib eine gültige E-Mail-Adresse ein.';
        return;
    }

    sending.value = true;
    try {
        const redirect = typeof route.query.weiter === 'string' ? route.query.weiter : '';
        await authApi.requestLink(value, redirect);
        save('wishling.lastEmail', value);
        sentTo.value = value;
    } catch (e) {
        error.value = e.message;
    } finally {
        sending.value = false;
    }
}

async function reset() {
    sentTo.value = '';
    await nextTick();
    emailInput.value?.focus();
}
</script>
