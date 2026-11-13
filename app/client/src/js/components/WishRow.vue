<template>
    <div class="items_row" :class="{ 'items_row--hidden': wish.hidden }" role="row">
        <div class="items_cell items_cell--priority" role="cell" data-label="Stärke">
            <WishPriority
                :model-value="wish.priority"
                :label="wish.title"
                @update:model-value="(priority) => $emit('quick-update', { priority })"
            />
        </div>
        <div class="items_cell items_cell--title" role="cell" data-label="Titel">
            <EditableCell :value="wish.title" label="Titel" required @save="(title) => onUpdate({ title })" />
        </div>
        <div class="items_cell items_cell--type" role="cell" data-label="Typ">
            <EditableCell
                :value="wish.type"
                label="Typ"
                :maxlength="100"
                placeholder="z. B. Brettspiel"
                list="wish-type-options"
                @save="(type) => onUpdate({ type })"
            >
                <template #default="{ value }">
                    <span class="wish_type">{{ value }}</span>
                </template>
            </EditableCell>
        </div>
        <div class="items_cell items_cell--description" role="cell" data-label="Beschreibung">
            <EditableCell
                :value="wish.description"
                label="Beschreibung"
                multiline
                :maxlength="5000"
                @save="(description) => onUpdate({ description })"
            />
        </div>
        <div class="items_cell items_cell--price" role="cell" data-label="Preis">
            <EditableCell
                :value="wish.price"
                label="Preis"
                :maxlength="100"
                placeholder="z. B. 25 € oder 20–30 €"
                @save="(price) => onUpdate({ price })"
            >
                <template #default="{ value }">{{ formatPrice(value) }}</template>
            </EditableCell>
        </div>
        <div class="items_cell items_cell--url" role="cell" data-label="Link">
            <EditableCell
                :value="wish.url"
                label="Link"
                input-type="url"
                :maxlength="2048"
                placeholder="https://…"
                @save="(url) => onUpdate({ url })"
            >
                <template #default="{ value }">
                    <span class="wish_link_text">{{ shortUrl(value) }}</span>
                </template>
            </EditableCell>
            <a
                v-if="wish.url"
                :href="wish.url"
                target="_blank"
                rel="noopener noreferrer"
                class="wish_link_open"
                :title="`Link öffnen: ${wish.url}`"
                :aria-label="`Link zu „${wish.title}“ in neuem Tab öffnen`"
            />
        </div>
        <div class="items_cell items_cell--actions" role="cell">
            <button
                type="button"
                class="button button--icon-only"
                :class="wish.hidden ? 'button--icon-visibility-off' : 'button--icon-visibility'"
                :aria-label="wish.hidden ? `„${wish.title}“ für Gäste wieder einblenden` : `„${wish.title}“ für Gäste ausblenden`"
                :aria-pressed="wish.hidden ? 'true' : 'false'"
                :title="wish.hidden ? 'Für Gäste ausgeblendet – zum Einblenden klicken' : 'Für Gäste ausblenden'"
                @click="$emit('quick-update', { hidden: !wish.hidden })"
            />
            <button
                type="button"
                class="button button--icon-archive button--icon-only"
                :aria-label="`„${wish.title}“ archivieren`"
                title="Archivieren"
                @click="$emit('archive')"
            />
        </div>
    </div>
</template>

<script setup>
import { formatPrice, shortUrl } from '@/utils/format';
import EditableCell from '@/components/EditableCell.vue';
import WishPriority from '@/components/WishPriority.vue';

defineProps({
    wish: { type: Object, required: true },
    // Wird als @update gebunden: speichert Textfelder, muss ein Promise liefern
    onUpdate: { type: Function, required: true },
});

// quick-update: Stärke und Sichtbarkeit (sofort anzeigen, bei Fehler zurücksetzen)
defineEmits(['quick-update', 'archive']);
</script>
