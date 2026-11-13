<template>
    <PageState
        v-if="loading || loadError"
        :loading="loading"
        :error="loadError"
        error-title="Wunschliste nicht gefunden"
    />

    <section v-else class="section--Wishlist">
        <div class="section_content">
            <div v-if="isOwner" class="owner_banner" role="note">
                <p>
                    Das ist deine Liste, so wie Gäste sie sehen.
                    Reservierungen werden dir nicht angezeigt, damit die Überraschung bleibt.
                </p>
                <RouterLink :to="{ name: 'wishlist-edit', params: { hash: wishlist.hash } }" class="button button--primary button--small">
                    Bearbeiten
                </RouterLink>
            </div>

            <div class="wishlist_meta_view">
                <div class="wishlist_meta_text">
                    <h1 class="wishlist_title">
                        {{ wishlist.title }}
                        <span v-if="wishlist.ownerName" class="wishlist_title_owner">
                        von
                        <UserAvatar v-if="wishlist.ownerAvatarUrl" :url="wishlist.ownerAvatarUrl" :name="wishlist.ownerName" size="inline" />
                        {{ wishlist.ownerName }}
                    </span>
                    </h1>
                    <p v-if="wishlist.description" class="wishlist_description">{{ wishlist.description }}</p>
                </div>
            </div>

            <template v-if="wishes.length">
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
                        <label for="guest-sort" class="wishlist_sort_label">Sortierung</label>
                        <select id="guest-sort" v-model="sort" class="wishlist_sort_select">
                            <option v-for="option in SORT_OPTIONS" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="wishlist_items wishlist_items--guest" role="table" aria-label="Wünsche">
                    <div class="items_row items_row--header" role="row">
                        <SortHeader label="Stärke" field="priority" sortable :state="sortState('priority')" @sort="toggleSort" />
                        <SortHeader label="Titel" field="title" sortable :state="sortState('title')" @sort="toggleSort" />
                        <SortHeader label="Typ" field="type" sortable :state="sortState('type')" @sort="toggleSort" />
                        <SortHeader label="Beschreibung" />
                        <SortHeader label="Preis" field="price" sortable :state="sortState('price')" @sort="toggleSort" />
                        <SortHeader label="Link" />
                        <SortHeader :label="isOwner ? '' : 'Reservierung'" />
                    </div>

                    <GuestWishRow
                        v-for="wish in visibleWishes"
                        :key="wish.hash"
                        :wish="wish"
                        :is-owner="isOwner"
                        @reserve="reserveTarget = wish"
                        @unreserve="releaseTarget = wish"
                        @history="historyTarget = wish"
                    />

                    <div v-if="visibleWishes.length === 0" class="items_empty" role="row">
                        <p role="cell">Keine Wünsche gefunden für „{{ search }}“.</p>
                    </div>
                </div>
            </template>
            <p v-else class="lists_empty">Diese Wunschliste enthält noch keine Wünsche.</p>

            <!-- Nur für Gäste: der Besitzer bekommt die Einträge gar nicht erst vom Server -->
            <GuestGifts
                v-if="!isOwner"
                :gifts="wishlist.guestGifts || []"
                :owner-name="wishlist.ownerName"
                @add="addGift"
                @remove="giftToRemove = $event"
            />
        </div>

        <ReserveDialog
            :wish="reserveTarget"
            :owner-name="wishlist.ownerName"
            @submit="reserve"
            @close="reserveTarget = null"
        />
        <ReleaseDialog :wish="releaseTarget" @submit="release" @close="releaseTarget = null" />
        <HistoryDialog :wish="historyTarget" @close="historyTarget = null" />
        <ConfirmDialog
            :open="!!giftToRemove"
            title="Eintrag entfernen?"
            :text="giftToRemove ? `„${giftToRemove.title}“ von ${giftToRemove.guestName} wird für alle Gäste entfernt.` : ''"
            confirm-label="Ja, entfernen"
            @confirm="removeGift"
            @close="giftToRemove = null"
        />
    </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { publicApi } from '@/api';
import { useToasts } from '@/composables/useToasts';
import { useWishFilter } from '@/composables/useWishFilter';
import { SORT_OPTIONS } from '@/utils/format';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import GuestGifts from '@/components/GuestGifts.vue';
import GuestWishRow from '@/components/GuestWishRow.vue';
import HistoryDialog from '@/components/HistoryDialog.vue';
import PageState from '@/components/PageState.vue';
import ReleaseDialog from '@/components/ReleaseDialog.vue';
import ReserveDialog from '@/components/ReserveDialog.vue';
import SortHeader from '@/components/SortHeader.vue';
import UserAvatar from '@/components/UserAvatar.vue';

const props = defineProps({
    hash: { type: String, required: true },
});

const toast = useToasts();

const wishlist = ref(null);
const isOwner = ref(false);
const loading = ref(true);
const loadError = ref('');
const reserveTarget = ref(null);
const releaseTarget = ref(null);
const historyTarget = ref(null);
const giftToRemove = ref(null);

const wishes = computed(() => wishlist.value?.wishes ?? []);
const { search, sort, visibleWishes, toggleSort, sortState } = useWishFilter(wishes);

watch(
    () => props.hash,
    async (hash) => {
        loading.value = true;
        loadError.value = '';
        try {
            const data = await publicApi.wishlist(hash);
            wishlist.value = data.wishlist;
            isOwner.value = data.isOwner;
            sort.value = data.wishlist.defaultSort;
            document.title = `${data.wishlist.title} – Wishling`;
        } catch (e) {
            loadError.value = e.status === 404
                ? 'Diese Wunschliste gibt es nicht (mehr). Prüfe, ob der Link vollständig ist.'
                : e.message;
        } finally {
            loading.value = false;
        }
    },
    { immediate: true },
);

function replaceWish(updated) {
    const index = wishlist.value.wishes.findIndex((wish) => wish.hash === updated.hash);
    if (index !== -1) {
        wishlist.value.wishes[index] = updated;
    }
}

async function reserve(data) {
    const wish = reserveTarget.value;
    try {
        const result = await publicApi.reserve(wish.hash, data);
        replaceWish(result.wish);
        toast.success(`Du hast „${wish.title}“ reserviert. Danke!`);
    } catch (e) {
        if (e.status === 409) {
            // Jemand anderes war schneller – Liste aktualisieren
            const fresh = await publicApi.wishlist(props.hash).catch(() => null);
            if (fresh) {
                wishlist.value = fresh.wishlist;
            }
        }
        throw e;
    }
}

/**
 * Reservierung freigeben (nach Bestätigung im Dialog, mit Namen für den Verlauf)
 */
async function release(data) {
    const wish = releaseTarget.value;
    try {
        const result = await publicApi.unreserve(wish.hash, data);
        replaceWish(result.wish);
        toast.success(`„${wish.title}“ ist wieder frei.`);
    } catch (e) {
        if (e.status === 409) {
            // Wurde inzwischen schon von jemand anderem freigegeben – Liste aktualisieren
            const fresh = await publicApi.wishlist(props.hash).catch(() => null);
            if (fresh) {
                wishlist.value = fresh.wishlist;
            }
        }
        throw e;
    }
}

async function addGift(data) {
    const { gift } = await publicApi.addGift(props.hash, data);
    wishlist.value.guestGifts = [...(wishlist.value.guestGifts || []), gift];
    toast.success(`„${gift.title}“ ist eingetragen.`);
}

async function removeGift() {
    const gift = giftToRemove.value;
    try {
        await publicApi.removeGift(gift.hash);
    } catch (e) {
        // Schon entfernt? Dann trotzdem aus der Anzeige nehmen
        if (e.status !== 404) {
            toast.error(e.message);
            throw e;
        }
    }
    wishlist.value.guestGifts = wishlist.value.guestGifts.filter((item) => item.hash !== gift.hash);
    toast.success(`„${gift.title}“ wurde entfernt.`);
}
</script>
