<template>
    <div class="items_row" :class="{ 'items_row--reserved': wish.reservation }" role="row">
        <div class="items_cell items_cell--priority" role="cell" data-label="Stärke">
            <WishPriority :model-value="wish.priority" readonly />
        </div>
        <div class="items_cell items_cell--title" role="cell" data-label="Titel">{{ wish.title }}</div>
        <div class="items_cell items_cell--type" role="cell" data-label="Typ">
            <span v-if="wish.type" class="wish_type">{{ wish.type }}</span>
        </div>
        <div class="items_cell items_cell--description" role="cell" data-label="Beschreibung">{{ wish.description }}</div>
        <div class="items_cell items_cell--price" role="cell" data-label="Preis">{{ formatPrice(wish.price) }}</div>
        <div class="items_cell items_cell--url" role="cell" data-label="Link">
            <WishLink :url="wish.url" />
        </div>
        <div class="items_cell items_cell--actions items_cell--reservation" role="cell">
            <template v-if="isOwner" />
            <template v-else-if="wish.reservation">
                <span class="reserved_label">Reserviert von {{ wish.reservation.guestName }}</span>
                <button
                    type="button"
                    class="button button--ghost button--small"
                    :disabled="busy"
                    :aria-label="`Reservierung für „${wish.title}“ aufheben`"
                    @click="$emit('unreserve')"
                >
                    Freigeben
                </button>
            </template>
            <button
                v-else
                type="button"
                class="button button--icon-reserve button--small"
                :disabled="busy"
                :aria-label="`„${wish.title}“ reservieren`"
                @click="$emit('reserve')"
            >
                Reservieren
            </button>
        </div>
    </div>
</template>

<script setup>
import { formatPrice } from '@/utils/format';
import WishLink from '@/components/WishLink.vue';
import WishPriority from '@/components/WishPriority.vue';

defineProps({
    wish: { type: Object, required: true },
    isOwner: { type: Boolean, default: false },
    busy: { type: Boolean, default: false },
});
defineEmits(['reserve', 'unreserve']);
</script>
