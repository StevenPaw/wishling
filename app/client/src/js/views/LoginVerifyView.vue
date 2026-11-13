<template>
    <PageState :loading="!error" :error="error" error-title="Anmeldung fehlgeschlagen">
        <template #actions>
            <RouterLink :to="{ name: 'login' }" class="button button--primary">Neuen Link anfordern</RouterLink>
        </template>
    </PageState>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { authApi } from '@/api';
import { useAuth } from '@/stores/auth';
import { useToasts } from '@/composables/useToasts';
import PageState from '@/components/PageState.vue';

const route = useRoute();
const router = useRouter();
const auth = useAuth();
const toast = useToasts();

const error = ref('');

onMounted(async () => {
    const token = typeof route.query.token === 'string' ? route.query.token : '';
    const redirect = typeof route.query.weiter === 'string' && route.query.weiter.startsWith('/') && !route.query.weiter.startsWith('//')
        ? route.query.weiter
        : '/meine-listen';

    if (!token) {
        error.value = 'Dieser Login-Link ist unvollständig.';
        return;
    }

    try {
        const { user } = await authApi.verify(token);
        auth.setUser(user);

        if (!user.displayName) {
            router.replace({ name: 'profile', query: { willkommen: '1', weiter: redirect } });
            return;
        }
        toast.success(`Schön, dass du da bist, ${user.displayName}!`);
        router.replace(redirect);
    } catch (e) {
        error.value = e.message;
    }
});
</script>
