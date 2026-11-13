<template>
    <section class="section--Lists">
        <div class="section_content">
            <h1>Meine Wunschlisten</h1>

            <PageState v-if="loading || loadError" :loading="loading" :error="loadError" />

            <template v-else>
                <div v-if="activeLists.length === 0" class="lists_empty">
                    <p>Du hast noch keine Wunschliste.</p>
                    <button type="button" class="button button--primary" @click="dialog.openCreate()">
                        Erste Wunschliste anlegen
                    </button>
                </div>

                <ul v-else class="lists_rows">
                    <li v-for="list in activeLists" :key="list.hash" class="list_row">
                        <RouterLink :to="{ name: 'wishlist-edit', params: { hash: list.hash } }" class="list_row_link">
                            <h2 class="list_row_title">{{ list.title }}</h2>
                            <p v-if="list.description" class="list_row_description">{{ list.description }}</p>
                            <p class="list_row_meta">
                                {{ list.wishCount === 1 ? '1 Wunsch' : `${list.wishCount} Wünsche` }}
                                <template v-if="list.hiddenCount"> · {{ list.hiddenCount }} ausgeblendet</template>
                            </p>
                        </RouterLink>
                        <div class="list_row_actions">
                            <ShareActions :url="list.shareUrl" :title="list.title" icon-only />
                            <button
                                type="button"
                                class="button button--icon-duplicate button--icon-only"
                                :aria-label="`„${list.title}“ duplizieren`"
                                title="Duplizieren"
                                @click="dialog.openDuplicate(list)"
                            />
                            <button
                                type="button"
                                class="button button--icon-archive button--icon-only"
                                :aria-label="`„${list.title}“ archivieren`"
                                title="Archivieren"
                                @click="archive(list)"
                            />
                        </div>
                    </li>
                </ul>

                <details v-if="archivedLists.length" class="archive_section">
                    <summary>Archivierte Listen ({{ archivedLists.length }})</summary>
                    <ul class="archive_list">
                        <li v-for="list in archivedLists" :key="list.hash" class="archive_item">
                            <span>{{ list.title }}</span>
                            <div class="archive_item_actions">
                                <button
                                    type="button"
                                    class="button button--icon-duplicate button--icon-only"
                                    :aria-label="`„${list.title}“ duplizieren`"
                                    title="Duplizieren"
                                    @click="dialog.openDuplicate(list)"
                                />
                                <button type="button" class="button button--small" @click="restore(list)">
                                    Wiederherstellen
                                </button>
                            </div>
                        </li>
                    </ul>
                </details>
            </template>
        </div>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { wishlistApi } from '@/api';
import { useToasts } from '@/composables/useToasts';
import { useWishlistDialog } from '@/composables/useWishlistDialog';
import PageState from '@/components/PageState.vue';
import ShareActions from '@/components/ShareActions.vue';

const toast = useToasts();
const dialog = useWishlistDialog();

const lists = ref([]);
const loading = ref(true);
const loadError = ref('');

const activeLists = computed(() => lists.value.filter((list) => !list.archived));
const archivedLists = computed(() => lists.value.filter((list) => list.archived));

onMounted(async () => {
    try {
        const data = await wishlistApi.list();
        lists.value = data.wishlists;
    } catch (e) {
        loadError.value = e.message;
    } finally {
        loading.value = false;
    }
});

function replaceList(updated) {
    const index = lists.value.findIndex((list) => list.hash === updated.hash);
    if (index !== -1) {
        lists.value[index] = updated;
    }
}

async function archive(list) {
    try {
        const { wishlist } = await wishlistApi.archive(list.hash);
        replaceList(wishlist);
        toast.success(`„${list.title}“ wurde archiviert.`, {
            action: { label: 'Rückgängig', handler: () => restore(wishlist, true) },
        });
    } catch (e) {
        toast.error(e.message);
    }
}

async function restore(list, silent = false) {
    try {
        const { wishlist } = await wishlistApi.restore(list.hash);
        replaceList(wishlist);
        if (!silent) {
            toast.success(`„${list.title}“ ist wieder aktiv.`);
        }
    } catch (e) {
        toast.error(e.message);
    }
}
</script>
