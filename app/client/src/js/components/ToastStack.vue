<template>
    <div class="toast_stack" aria-live="polite" aria-atomic="false">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="toast"
                :class="`toast--${toast.type}`"
                :role="toast.type === 'error' ? 'alert' : 'status'"
            >
                <span class="toast_message">{{ toast.message }}</span>
                <button
                    v-if="toast.action"
                    type="button"
                    class="toast_action"
                    @click="runAction(toast)"
                >
                    {{ toast.action.label }}
                </button>
                <button
                    type="button"
                    class="toast_close"
                    aria-label="Meldung schließen"
                    @click="dismiss(toast.id)"
                >
                    ×
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { useToasts } from '@/composables/useToasts';

const { toasts, dismiss, runAction } = useToasts();
</script>
