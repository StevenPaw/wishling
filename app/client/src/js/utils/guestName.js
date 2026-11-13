import { load, save } from '@/utils/storage';

/**
 * Name des Gastes, im Browser gemerkt für Reservierungen, Freigaben und weitere Geschenke
 */
const KEY = 'wishling.guestName';

export const loadGuestName = () => load(KEY, '');
export const saveGuestName = (name) => save(KEY, name.trim());
