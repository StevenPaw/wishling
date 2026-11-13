const currency = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' });
const dateTime = new Intl.DateTimeFormat('de-DE', { dateStyle: 'medium', timeStyle: 'short' });
const date = new Intl.DateTimeFormat('de-DE', { dateStyle: 'medium' });

/**
 * ISO-Zeitstempel aus der API, z. B. "13.11.2026, 19:03"
 */
export function formatDateTime(iso) {
    const value = new Date(iso);
    return Number.isNaN(value.getTime()) ? '' : dateTime.format(value);
}

/**
 * ISO-Zeitstempel aus der API, nur Datum, z. B. "13.11.2026"
 */
export function formatDate(iso) {
    const value = new Date(iso);
    return Number.isNaN(value.getTime()) ? '' : date.format(value);
}

/**
 * Erste Zahl aus einem Preis-Text, z. B. "ca. 20–30 €" -> 20, "12,50" -> 12.5.
 * Gibt null zurück, wenn keine Zahl enthalten ist.
 */
export function parsePrice(value) {
    if (!value) {
        return null;
    }
    const match = String(value).match(/\d+(?:[.,]\d+)?/);
    if (!match) {
        return null;
    }
    return parseFloat(match[0].replace(',', '.'));
}

/**
 * Formatiert reine Zahlen als Euro-Betrag, Freitext bleibt unverändert.
 */
export function formatPrice(value) {
    const text = String(value ?? '').trim();
    if (/^\d+(?:[.,]\d{1,2})?$/.test(text)) {
        return currency.format(parseFloat(text.replace(',', '.')));
    }
    return text;
}

/**
 * Kurzform eines Links für die Anzeige (Domain ohne www)
 */
export function shortUrl(url) {
    try {
        return new URL(url).hostname.replace(/^www\./, '');
    } catch {
        return url;
    }
}

export const SORT_OPTIONS = [
    { value: '', label: 'Reihenfolge des Anlegens' },
    { value: 'created-desc', label: 'Neueste zuerst' },
    { value: 'priority-desc', label: 'Stärke (stärkste zuerst)' },
    { value: 'priority-asc', label: 'Stärke (schwächste zuerst)' },
    { value: 'title-asc', label: 'Titel (A–Z)' },
    { value: 'title-desc', label: 'Titel (Z–A)' },
    { value: 'type-asc', label: 'Typ (A–Z)' },
    { value: 'type-desc', label: 'Typ (Z–A)' },
    { value: 'price-asc', label: 'Preis (aufsteigend)' },
    { value: 'price-desc', label: 'Preis (absteigend)' },
];
