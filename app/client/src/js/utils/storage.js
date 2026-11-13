/**
 * localStorage-Zugriff, der in privaten Fenstern o. Ä. nicht abstürzt
 */
export function load(key, fallback = null) {
    try {
        const value = window.localStorage.getItem(key);
        return value === null ? fallback : JSON.parse(value);
    } catch {
        return fallback;
    }
}

export function save(key, value) {
    try {
        window.localStorage.setItem(key, JSON.stringify(value));
    } catch {
        // ignorieren
    }
}
