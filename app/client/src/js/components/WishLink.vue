<template>
    <a
        v-if="url"
        :href="url"
        target="_blank"
        rel="noopener noreferrer"
        class="wish_link"
        :title="url"
    >
        <span class="wish_link_text">{{ shortUrl(url) }}</span>
        <span class="wish_link_icon" aria-hidden="true" />
        <span class="sr-only">(öffnet in neuem Tab)</span>
    </a>
    <span v-else class="items_placeholder" aria-label="Kein Link">–</span>
</template>

<script setup>
import { shortUrl } from '@/utils/format';

defineProps({
    url: { type: String, default: '' },
});
</script>
