<template>
    <AppDialog :open="!!wish" labelledby="history-title" @close="$emit('close')">
        <div v-if="wish" class="form">
            <div class="dialog_header">
                <h2 id="history-title">Verlauf: „{{ wish.title }}“</h2>
                <button type="button" class="dialog_close" aria-label="Schließen" @click="$emit('close')">×</button>
            </div>
            <p class="dialog_text">
                Nur für Gäste sichtbar. Neueste Einträge zuerst.
                <template v-if="!wish.history?.length">Bisher wurde dieser Wunsch noch nicht reserviert.</template>
            </p>

            <ol class="history_list">
                <li
                    v-for="(entry, index) in wish.history || []"
                    :key="index"
                    class="history_entry"
                    :class="`history_entry--${entry.action}`"
                >
                    <time class="history_time" :datetime="entry.created">{{ formatDateTime(entry.created) }}</time>
                    <span class="history_text">
                        {{ entry.action === 'reserved' ? 'Reserviert von' : 'Freigegeben von' }}
                        <strong>{{ entry.guestName }}</strong>
                    </span>
                </li>
                <!-- Ältester Eintrag: wann der Wunsch auf die Liste kam -->
                <li class="history_entry history_entry--created">
                    <time class="history_time" :datetime="wish.created">{{ formatDateTime(wish.created) }}</time>
                    <span class="history_text">Wunsch eingetragen</span>
                </li>
            </ol>

            <div class="form_actions">
                <button type="button" class="button button--ghost" @click="$emit('close')">Schließen</button>
            </div>
        </div>
    </AppDialog>
</template>

<script setup>
import { formatDateTime } from '@/utils/format';
import AppDialog from '@/components/AppDialog.vue';

defineProps({
    wish: { type: Object, default: null },
});
defineEmits(['close']);
</script>
