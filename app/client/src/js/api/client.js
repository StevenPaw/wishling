/**
 * Fehler aus der Wishling-API (enthält HTTP-Status und deutsche Fehlermeldung)
 */
export class ApiError extends Error {
    constructor(message, status, data = null) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.data = data;
    }
}

/**
 * Zentrale Fetch-Funktion für alle API-Aufrufe.
 * Sendet den CSRF-Header und wirft bei Fehlern einen ApiError.
 */
export async function request(method, path, body) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
    const options = { method, headers, credentials: 'same-origin' };

    if (body instanceof FormData) {
        // Datei-Upload: Content-Type (inkl. Boundary) setzt der Browser selbst
        options.body = body;
    } else if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }

    let response;
    try {
        response = await fetch(`/api/${path}`, options);
    } catch {
        throw new ApiError('Keine Verbindung zum Server. Bitte prüfe deine Internetverbindung.', 0);
    }

    let data = null;
    try {
        data = await response.json();
    } catch {
        // leere oder ungültige Antwort
    }

    if (!response.ok) {
        throw new ApiError(data?.error || `Unerwarteter Fehler (${response.status}).`, response.status, data);
    }
    return data;
}

export const get = (path) => request('GET', path);
export const post = (path, body = {}) => request('POST', path, body);
export const put = (path, body = {}) => request('PUT', path, body);
export const del = (path, body) => request('DELETE', path, body);
