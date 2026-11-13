<template>
    <section class="section--Home">
        <div class="section_content">
            <img class="home_logo" src="/_resources/app/client/icons/wishling_logo.svg" alt="Wishling">
            <h1 class="home_title">Wunschlisten, die einfach funktionieren.</h1>
            <p class="home_text">
                Erstelle deine Wunschliste, teile den Link mit Familie und Freunden –
                und sie reservieren Geschenke, ohne dass du siehst, wer was besorgt.
            </p>
            <div class="home_actions">
                <RouterLink v-if="auth.state.user" :to="{ name: 'my-lists' }" class="button button--primary button--large">
                    Zu meinen Listen
                </RouterLink>
                <template v-else>
                    <RouterLink :to="{ name: 'login' }" class="button button--primary button--large">
                        Kostenlos starten
                    </RouterLink>
                    <p class="home_hint">Kein Passwort nötig – du bekommst einen Login-Link per E-Mail.</p>
                </template>
            </div>
        </div>
    </section>
</template>

<script setup>
import { useAuth } from '@/stores/auth';

const auth = useAuth();
</script>
