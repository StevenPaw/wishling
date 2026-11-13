import { reactive, readonly } from 'vue';

/**
 * Globaler Zustand für das Modal "Neue Wunschliste" / "Wunschliste duplizieren".
 * Das Modal selbst hängt einmalig in App.vue.
 */
const state = reactive({
    open: false,
    // Liste, die dupliziert werden soll (null = neue, leere Liste)
    source: null,
});

export function useWishlistDialog() {
    return {
        state: readonly(state),
        openCreate() {
            state.source = null;
            state.open = true;
        },
        openDuplicate(list) {
            state.source = { ...list };
            state.open = true;
        },
        close() {
            state.open = false;
        },
    };
}
