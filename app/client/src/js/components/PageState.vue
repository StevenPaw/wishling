<template>
    <section class="section--State">
        <div class="section_content">
            <p v-if="loading" class="page_state page_state--loading" role="status">Lädt …</p>
            <div v-else-if="error" class="page_state page_state--error" role="alert">
                <h1>{{ errorTitle }}</h1>
                <p>{{ error }}</p>
                <slot name="actions">
                    <RouterLink to="/" class="button">Zur Startseite</RouterLink>
                </slot>
            </div>
        </div>
    </section>
</template>

<script setup>
defineProps({
    loading: { type: Boolean, default: false },
    error: { type: String, default: '' },
    errorTitle: { type: String, default: 'Das hat nicht geklappt' },
});
</script>
