<template>
    <PageState v-if="loading || loadError" :loading="loading" :error="loadError" error-title="Liste nicht gefunden">
        <template #actions>
            <RouterLink :to="{ name: 'my-lists' }" class="button">Zu meinen Listen</RouterLink>
        </template>
    </PageState>

    <section v-else class="section--Wishlist">
        <div class="section_content">
            <WishlistMeta :wishlist="wishlist" :saving="savingMeta" @save="saveMeta" />

            <div class="wishlist_toolbar">
                <ShareActions :url="wishlist.shareUrl" :title="wishlist.title" />
                <RouterLink :to="{ name: 'wishlist-guest', params: { hash: wishlist.hash } }" class="button button--ghost">
                    Als Gast ansehen
                </RouterLink>
            </div>

            <div class="wishlist_controls">
                <label for="wish-search" class="sr-only">Wünsche durchsuchen</label>
                <input
                    id="wish-search"
                    v-model="search"
                    type="search"
                    class="wish_search"
                    placeholder="Suche nach Titel, Typ oder Beschreibung …"
                >
                <div class="wishlist_sort">
                    <label for="default-sort" class="wishlist_sort_label">Sortierung für Gäste</label>
                    <select
                        id="default-sort"
                        class="wishlist_sort_select"
                        :value="wishlist.defaultSort"
                        @change="saveDefaultSort($event.target.value)"
                    >
                        <option v-for="option in SORT_OPTIONS" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="wishlist_items wishlist_items--owner" role="table" aria-label="Wünsche">
                <div class="items_row items_row--header" role="row">
                    <SortHeader label="Stärke" field="priority" sortable :state="sortState('priority')" @sort="toggleSort" />
                    <SortHeader label="Titel" field="title" sortable :state="sortState('title')" @sort="toggleSort" />
                    <SortHeader label="Typ" field="type" sortable :state="sortState('type')" @sort="toggleSort" />
                    <SortHeader label="Beschreibung" />
                    <SortHeader label="Preis" field="price" sortable :state="sortState('price')" @sort="toggleSort" />
                    <SortHeader label="Link" />
                    <div class="items_cell items_cell--header items_cell--actions" role="columnheader">
                        <span class="sr-only">Aktionen</span>
                    </div>
                </div>

                <WishRow
                    v-for="wish in visibleWishes"
                    :key="wish.hash"
                    :wish="wish"
                    @update="(data) => saveWish(wish, data)"
                    @quick-update="(data) => quickUpdate(wish, data)"
                    @archive="archiveWish(wish)"
                />

                <div v-if="wishes.length === 0" class="items_empty" role="row">
                    <p role="cell">Füge jetzt deinen ersten Wunsch hinzu!</p>
                </div>
                <div v-else-if="visibleWishes.length === 0" class="items_empty" role="row">
                    <p role="cell">Keine Wünsche gefunden für „{{ search }}“.</p>
                </div>

                <WishAddRow @add="addWish" />
            </div>

            <!-- Vorschläge für das Typ-Feld: alle Typen, die in dieser Liste schon vorkommen -->
            <datalist id="wish-type-options">
                <option v-for="type in typeOptions" :key="type" :value="type" />
            </datalist>

            <details v-if="wishlist.archivedWishes.length" class="archive_section">
                <summary>Archivierte Wünsche ({{ wishlist.archivedWishes.length }})</summary>
                <ul class="archive_list">
                    <li v-for="wish in wishlist.archivedWishes" :key="wish.hash" class="archive_item">
                        <span>{{ wish.title }}</span>
                        <button type="button" class="button button--small" @click="restoreWish(wish)">Wiederherstellen</button>
                    </li>
                </ul>
            </details>
        </div>
    </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { wishApi, wishlistApi } from '@/api';
import { useToasts } from '@/composables/useToasts';
import { useWishFilter } from '@/composables/useWishFilter';
import { SORT_OPTIONS } from '@/utils/format';
import PageState from '@/components/PageState.vue';
import ShareActions from '@/components/ShareActions.vue';
import SortHeader from '@/components/SortHeader.vue';
import WishAddRow from '@/components/WishAddRow.vue';
import WishlistMeta from '@/components/WishlistMeta.vue';
import WishRow from '@/components/WishRow.vue';

const props = defineProps({
    hash: { type: String, required: true },
});

const toast = useToasts();

const wishlist = ref(null);
const loading = ref(true);
const loadError = ref('');
const savingMeta = ref(false);

const wishes = computed(() => wishlist.value?.wishes ?? []);
const typeOptions = computed(() => {
    const all = [...wishes.value, ...(wishlist.value?.archivedWishes ?? [])]
        .map((wish) => wish.type?.trim())
        .filter(Boolean);
    return [...new Set(all)].sort((a, b) => a.localeCompare(b, 'de'));
});
const { search, sort, visibleWishes, toggleSort, sortState } = useWishFilter(wishes);

watch(
    () => props.hash,
    async (hash) => {
        loading.value = true;
        loadError.value = '';
        try {
            const data = await wishlistApi.show(hash);
            wishlist.value = data.wishlist;
            sort.value = data.wishlist.defaultSort;
            document.title = `${data.wishlist.title} bearbeiten – Wishling`;
        } catch (e) {
            loadError.value = e.message;
        } finally {
            loading.value = false;
        }
    },
    { immediate: true },
);

async function saveMeta(data) {
    savingMeta.value = true;
    try {
        const result = await wishlistApi.update(props.hash, data);
        Object.assign(wishlist.value, {
            title: result.wishlist.title,
            description: result.wishlist.description,
        });
        document.title = `${result.wishlist.title} bearbeiten – Wishling`;
        toast.success('Liste gespeichert.', { timeout: 2500 });
    } catch (e) {
        toast.error(e.message);
        throw e;
    } finally {
        savingMeta.value = false;
    }
}

async function saveDefaultSort(value) {
    const previous = wishlist.value.defaultSort;
    wishlist.value.defaultSort = value;
    sort.value = value;
    try {
        await wishlistApi.update(props.hash, { defaultSort: value });
        toast.success('Sortierung für Gäste gespeichert.', { timeout: 2500 });
    } catch (e) {
        wishlist.value.defaultSort = previous;
        toast.error(e.message);
    }
}

function replaceWish(list, updated) {
    const index = list.findIndex((wish) => wish.hash === updated.hash);
    if (index !== -1) {
        list[index] = updated;
    }
}

function moveWish(from, to, wish) {
    const index = from.findIndex((item) => item.hash === wish.hash);
    if (index !== -1) {
        from.splice(index, 1);
    }
    to.push(wish);
    to.sort((a, b) => a.created.localeCompare(b.created));
}

async function addWish(data) {
    try {
        const { wish } = await wishlistApi.addWish(props.hash, data);
        wishlist.value.wishes.push(wish);
        toast.success(`„${wish.title}“ hinzugefügt.`, { timeout: 2500 });
    } catch (e) {
        toast.error(e.message);
        throw e;
    }
}

/**
 * Speichert ein einzelnes Textfeld aus der Zelle. Bei Fehler wird geworfen,
 * damit die Zelle offen bleibt und man korrigieren kann.
 */
async function saveWish(wish, data) {
    try {
        const result = await wishApi.update(wish.hash, data);
        replaceWish(wishlist.value.wishes, result.wish);
    } catch (e) {
        toast.error(e.message);
        throw e;
    }
}

/**
 * Stärke und Sichtbarkeit: sofort anzeigen, bei Fehler zurücksetzen
 */
async function quickUpdate(wish, data) {
    const previous = Object.fromEntries(Object.keys(data).map((key) => [key, wish[key]]));
    Object.assign(wish, data);
    try {
        const result = await wishApi.update(wish.hash, data);
        replaceWish(wishlist.value.wishes, result.wish);
        if ('hidden' in data) {
            toast.success(
                data.hidden ? `„${wish.title}“ ist für Gäste ausgeblendet.` : `„${wish.title}“ ist für Gäste wieder sichtbar.`,
                { timeout: 2500 },
            );
        }
    } catch (e) {
        Object.assign(wish, previous);
        toast.error(e.message);
    }
}

async function archiveWish(wish) {
    try {
        const result = await wishApi.archive(wish.hash);
        moveWish(wishlist.value.wishes, wishlist.value.archivedWishes, result.wish);
        toast.success(`„${wish.title}“ wurde archiviert.`, {
            action: { label: 'Rückgängig', handler: () => restoreWish(result.wish, true) },
        });
    } catch (e) {
        toast.error(e.message);
    }
}

async function restoreWish(wish, silent = false) {
    try {
        const result = await wishApi.restore(wish.hash);
        moveWish(wishlist.value.archivedWishes, wishlist.value.wishes, result.wish);
        if (!silent) {
            toast.success(`„${wish.title}“ ist wieder auf der Liste.`, { timeout: 2500 });
        }
    } catch (e) {
        toast.error(e.message);
    }
}
</script>
