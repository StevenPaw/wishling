<template>
    <div
        v-if="readonly"
        class="wish_priority wish_priority--readonly"
        role="img"
        :aria-label="description"
        :title="description"
    >
        <span
            v-for="level in MAX"
            :key="level"
            class="wish_priority_bar"
            :class="[`wish_priority_bar--${level}`, { 'is-filled': level <= modelValue }]"
        />
    </div>

    <div
        v-else
        class="wish_priority"
        role="group"
        :aria-label="label ? `Stärke von „${label}“` : 'Stärke'"
    >
        <button
            v-for="level in MAX"
            :key="level"
            type="button"
            class="wish_priority_bar"
            :class="[`wish_priority_bar--${level}`, { 'is-filled': level <= modelValue }]"
            :aria-label="level === modelValue ? `Stärke ${level} von ${MAX}, zum Zurücksetzen klicken` : `Stärke ${level} von ${MAX}`"
            :aria-pressed="level <= modelValue ? 'true' : 'false'"
            :title="level === modelValue ? 'Stärke zurücksetzen' : `Stärke ${level} von ${MAX}`"
            @click="select(level)"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Stärke eines Wunsches als 5 Balken (0 = keine Angabe).
 * Klick auf Balken n setzt die Stärke auf n, Klick auf den aktuell höchsten setzt sie auf 0.
 */
// Muss zu Wish::PRIORITY_MAX im Backend passen
const MAX = 5;

const props = defineProps({
    modelValue: { type: Number, default: 0 },
    readonly: { type: Boolean, default: false },
    // Titel des Wunsches, für Screenreader
    label: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);

const description = computed(() => (props.modelValue ? `Stärke ${props.modelValue} von ${MAX}` : 'Keine Stärke angegeben'));

function select(level) {
    emit('update:modelValue', level === props.modelValue ? 0 : level);
}
</script>
