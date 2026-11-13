import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '@/stores/auth';

import HomeView from '@/views/HomeView.vue';
import LoginView from '@/views/LoginView.vue';
import LoginVerifyView from '@/views/LoginVerifyView.vue';
import MyListsView from '@/views/MyListsView.vue';
import WishlistEditView from '@/views/WishlistEditView.vue';
import WishlistGuestView from '@/views/WishlistGuestView.vue';
import ProfileView from '@/views/ProfileView.vue';
import NotFoundView from '@/views/NotFoundView.vue';

// Hinweis: Neue Top-Level-Pfade müssen auch in app/_config/routes.yml
// auf den SpaController zeigen, sonst liefert der Server beim Neuladen 404.
const routes = [
    { path: '/', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: LoginView, meta: { title: 'Anmelden', guestOnly: true } },
    { path: '/login/bestaetigen', name: 'login-verify', component: LoginVerifyView, meta: { title: 'Anmeldung' } },
    { path: '/meine-listen', name: 'my-lists', component: MyListsView, meta: { title: 'Meine Listen', requiresAuth: true } },
    { path: '/meine-listen/:hash', name: 'wishlist-edit', component: WishlistEditView, props: true, meta: { requiresAuth: true } },
    { path: '/liste/:hash', name: 'wishlist-guest', component: WishlistGuestView, props: true },
    { path: '/profil', name: 'profile', component: ProfileView, meta: { title: 'Profil', requiresAuth: true } },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundView, meta: { title: 'Nicht gefunden' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach(async (to) => {
    const auth = useAuth();
    const user = await auth.ensureLoaded();

    if (to.meta.requiresAuth && !user) {
        return { name: 'login', query: { weiter: to.fullPath } };
    }
    if (to.meta.guestOnly && user) {
        return { name: 'my-lists' };
    }
    return true;
});

router.afterEach((to) => {
    if (to.meta.title) {
        document.title = `${to.meta.title} – Wishling`;
    } else if (to.name === 'home') {
        document.title = 'Wishling';
    }
});

export default router;
