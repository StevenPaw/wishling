<template>
    <div class="items_cell items_cell--header" role="columnheader" :aria-sort="sortable ? state : undefined">
        <button
            v-if="sortable"
            type="button"
            class="sort_button"
            :class="`sort_button--${state}`"
            @click="$emit('sort', field)"
        >
            {{ label }}
            <span class="sort_icon" aria-hidden="true">{{ icons[state] }}</span>
        </button>
        <template v-else>{{ label }}</template>
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    field: { type: String, default: '' },
    sortable: { type: Boolean, default: false },
    state: { type: String, default: 'none' },
});
defineEmits(['sort']);

// Der Sortierzustand wird Screenreadern über aria-sort angesagt, die Pfeile sind nur Optik
const icons = { none: '⇅', ascending: '↑', descending: '↓' };
</script>
