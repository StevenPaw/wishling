import { reactive, readonly } from 'vue';

const toasts = reactive([]);
let nextId = 1;

// Mehr gleichzeitig sichtbare Meldungen verdecken nur die Oberfläche
const MAX_VISIBLE = 3;

/**
 * Zeigt eine Meldung an.
 * @param {string} message
 * @param {object} options type: 'success' | 'error' | 'info', timeout in ms,
 *                         action: { label, handler } z. B. für "Rückgängig"
 */
function show(message, { type = 'info', timeout = 4000, action = null } = {}) {
    const id = nextId++;
    toasts.push({ id, message, type, action });
    while (toasts.length > MAX_VISIBLE) {
        toasts.shift();
    }
    if (timeout > 0) {
        setTimeout(() => dismiss(id), action ? Math.max(timeout, 6000) : timeout);
    }
    return id;
}

function dismiss(id) {
    const index = toasts.findIndex((toast) => toast.id === id);
    if (index !== -1) {
        toasts.splice(index, 1);
    }
}

async function runAction(toast) {
    dismiss(toast.id);
    await toast.action?.handler();
}

export function useToasts() {
    return {
        toasts: readonly(toasts),
        show,
        success: (message, options = {}) => show(message, { ...options, type: 'success' }),
        error: (message, options = {}) => show(message, { timeout: 6000, ...options, type: 'error' }),
        dismiss,
        runAction,
    };
}
