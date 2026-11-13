<template>
    <span class="user_avatar" :class="`user_avatar--${size}`" aria-hidden="true">
        <img v-if="url" :src="url" alt="" class="user_avatar_image" draggable="false">
        <template v-else>{{ initial }}</template>
    </span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Rundes Profilbild, ersatzweise der Anfangsbuchstabe des Namens.
 * Rein dekorativ – der Name steht immer daneben oder im aria-label des Elternelements.
 */
const props = defineProps({
    url: { type: String, default: '' },
    name: { type: String, default: '' },
    // inline | small | large
    size: { type: String, default: 'small' },
});

const initial = computed(() => (props.name.trim().charAt(0) || '?').toUpperCase());
</script>
