import { reactive, readonly } from 'vue';
import { authApi } from '@/api';

const state = reactive({
    user: null,
    loaded: false,
});

let loadingPromise = null;

/**
 * Lädt den eingeloggten User einmalig (z. B. im Router-Guard).
 */
function ensureLoaded() {
    if (state.loaded) {
        return Promise.resolve(state.user);
    }
    if (!loadingPromise) {
        loadingPromise = authApi
            .me()
            .then((data) => {
                state.user = data.user;
            })
            .catch(() => {
                state.user = null;
            })
            .finally(() => {
                state.loaded = true;
                loadingPromise = null;
            });
    }
    return loadingPromise.then(() => state.user);
}

function setUser(user) {
    state.user = user;
    state.loaded = true;
}

async function logout() {
    try {
        await authApi.logout();
    } finally {
        setUser(null);
    }
}

export function useAuth() {
    return {
        state: readonly(state),
        ensureLoaded,
        setUser,
        logout,
    };
}
