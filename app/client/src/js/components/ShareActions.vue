<template>
    <div class="share_actions">
        <button
            v-if="canShare"
            type="button"
            class="button button--icon-share"
            :class="{ 'button--icon-only': iconOnly }"
            :aria-label="iconOnly ? labelFor('teilen') : undefined"
            :title="iconOnly ? 'Teilen' : undefined"
            @click="share"
        >
            <template v-if="!iconOnly">Teilen</template>
        </button>
        <button
            v-else
            type="button"
            class="button button--icon-link"
            :class="{ 'button--icon-only': iconOnly }"
            :aria-label="iconOnly ? labelFor('Link kopieren') : undefined"
            :title="iconOnly ? 'Link kopieren' : undefined"
            @click="copy"
        >
            <template v-if="!iconOnly">Link kopieren</template>
        </button>
    </div>
</template>

<script setup>
import { useToasts } from '@/composables/useToasts';

/**
 * Ein einziger Button zum Weitergeben des Links:
 * - Mit Web Share API (Windows, macOS, Android, iOS): Teilen-Dialog des Systems,
 *   der selbst "Link kopieren" anbietet
 * - Ohne (z. B. Firefox am Desktop): kopiert den Link direkt in die Zwischenablage
 */
const props = defineProps({
    url: { type: String, required: true },
    title: { type: String, default: '' },
    // Nur das Icon zeigen (Beschriftung dann über aria-label und Tooltip)
    iconOnly: { type: Boolean, default: false },
});

const toast = useToasts();
const canShare = typeof navigator !== 'undefined' && typeof navigator.share === 'function';

function labelFor(action) {
    if (!props.title) {
        return action.charAt(0).toUpperCase() + action.slice(1);
    }
    return action === 'teilen' ? `„${props.title}“ teilen` : `Link zu „${props.title}“ kopieren`;
}

async function copy() {
    try {
        await navigator.clipboard.writeText(props.url);
        toast.success('Link kopiert – du kannst ihn jetzt verschicken.');
    } catch {
        window.prompt('Link zum Kopieren:', props.url);
    }
}

async function share() {
    try {
        await navigator.share({
            title: props.title || 'Meine Wunschliste',
            text: 'Schau dir meine Wunschliste an!',
            url: props.url,
        });
    } catch (error) {
        // Abbrechen durch den Nutzer ist kein Fehler; sonst ersatzweise kopieren
        if (error?.name !== 'AbortError') {
            copy();
        }
    }
}
</script>
