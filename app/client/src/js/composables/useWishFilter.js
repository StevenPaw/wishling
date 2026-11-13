import { computed, ref } from 'vue';
import { parsePrice } from '@/utils/format';

/**
 * Suche und Sortierung für eine Wunschliste.
 * Sortier-Schlüssel: '' (Reihenfolge des Anlegens), 'title-asc', 'title-desc', 'type-asc', 'type-desc',
 * 'priority-desc', 'priority-asc',
 * 'price-asc', 'price-desc', 'created-desc'
 */
export function useWishFilter(wishes, initialSort = '') {
    const search = ref('');
    const sort = ref(initialSort);

    const collator = new Intl.Collator('de', { sensitivity: 'base', numeric: true });

    const visibleWishes = computed(() => {
        const term = search.value.trim().toLowerCase();
        let result = wishes.value.filter((wish) => {
            if (!term) {
                return true;
            }
            return `${wish.title} ${wish.type} ${wish.description}`.toLowerCase().includes(term);
        });

        const [field, direction] = sort.value.split('-');
        const factor = direction === 'desc' ? -1 : 1;

        if (field === 'title') {
            result = [...result].sort((a, b) => factor * collator.compare(a.title, b.title));
        } else if (field === 'priority') {
            result = [...result].sort((a, b) => factor * (a.priority - b.priority) || collator.compare(a.title, b.title));
        } else if (field === 'type') {
            // Wünsche ohne Typ landen immer am Ende, innerhalb eines Typs nach Titel
            result = [...result].sort((a, b) => {
                if (!a.type && !b.type) return collator.compare(a.title, b.title);
                if (!a.type) return 1;
                if (!b.type) return -1;
                return factor * collator.compare(a.type, b.type) || collator.compare(a.title, b.title);
            });
        } else if (field === 'price') {
            // Wünsche ohne erkennbaren Preis landen immer am Ende
            result = [...result].sort((a, b) => {
                const pa = parsePrice(a.price);
                const pb = parsePrice(b.price);
                if (pa === null && pb === null) return 0;
                if (pa === null) return 1;
                if (pb === null) return -1;
                return factor * (pa - pb);
            });
        } else if (field === 'created') {
            result = [...result].sort((a, b) => factor * a.created.localeCompare(b.created));
        }

        return result;
    });

    /**
     * Klick auf eine Spaltenüberschrift: gleiche Spalte wechselt die Richtung
     */
    function toggleSort(field) {
        const [current, direction] = sort.value.split('-');
        // Bei der Stärke ist "stärkste zuerst" die sinnvollere erste Richtung
        const first = field === 'priority' ? 'desc' : 'asc';
        const second = first === 'asc' ? 'desc' : 'asc';
        sort.value = current === field && direction === first ? `${field}-${second}` : `${field}-${first}`;
    }

    function sortState(field) {
        const [current, direction] = sort.value.split('-');
        if (current !== field) {
            return 'none';
        }
        return direction === 'desc' ? 'descending' : 'ascending';
    }

    return { search, sort, visibleWishes, toggleSort, sortState };
}
