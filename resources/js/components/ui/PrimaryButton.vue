<script setup>
defineProps({
    type: { type: String, default: 'button' },
    wide: { type: Boolean, default: false },
    to: { type: [String, Object], default: '' },
    disabled: { type: Boolean, default: false },
});
</script>

<template>
    <RouterLink v-if="to" :to="to" class="primary-button" :class="{ wide }">
        <slot />
    </RouterLink>
    <button v-else class="primary-button" :class="{ wide }" :type="type" :disabled="disabled">
        <slot />
    </button>
</template>
