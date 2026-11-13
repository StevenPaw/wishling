<template>
    <div class="app">
        <AppHeader />
        <main id="main" class="area_content main">
            <RouterView />
        </main>
        <AppFooter />
        <WishlistDialog v-if="auth.state.user" />
        <ToastStack />
    </div>
</template>

<script setup>
import { useAuth } from '@/stores/auth';
import AppHeader from '@/components/AppHeader.vue';
import AppFooter from '@/components/AppFooter.vue';
import ToastStack from '@/components/ToastStack.vue';
import WishlistDialog from '@/components/WishlistDialog.vue';

const auth = useAuth();
</script>
