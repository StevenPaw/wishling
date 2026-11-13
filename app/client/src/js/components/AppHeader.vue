<template>
    <header class="area_header">
        <div class="section_content">
            <RouterLink to="/" class="logo">
                <img src="/_resources/app/client/icons/wishling_logo.svg" alt="Wishling – zur Startseite">
            </RouterLink>
            <nav class="header_nav" aria-label="Hauptnavigation">
                <template v-if="auth.state.user">
                    <RouterLink :to="{ name: 'my-lists' }" class="header_link">Meine Listen</RouterLink>
                    <RouterLink
                        :to="{ name: 'profile' }"
                        class="header_link header_link--profile"
                        :aria-label="`Profil von ${displayName}`"
                    >
                        <UserAvatar :url="auth.state.user.avatarUrl || ''" :name="displayName" size="small" />
                        <span class="header_profile_name" aria-hidden="true">{{ displayName }}</span>
                    </RouterLink>
                    <button
                        type="button"
                        class="button button--primary button--icon-add header_new"
                        aria-label="Neue Wunschliste"
                        @click="dialog.openCreate()"
                    >
                        <span class="header_new_label" aria-hidden="true">Neue Wunschliste</span>
                    </button>
                </template>
                <RouterLink v-else-if="auth.state.loaded" :to="{ name: 'login' }" class="button button--primary">
                    Anmelden
                </RouterLink>
            </nav>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { useAuth } from '@/stores/auth';
import { useWishlistDialog } from '@/composables/useWishlistDialog';
import UserAvatar from '@/components/UserAvatar.vue';

const auth = useAuth();
const dialog = useWishlistDialog();

const displayName = computed(() => auth.state.user?.displayName || 'Profil');
</script>
