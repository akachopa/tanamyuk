<script setup>
defineProps({
    icon: { type: String, default: 'i-leaf' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="empty-state">
        <div class="stat-icon">
            <svg><use :href="`#${icon}`"></use></svg>
        </div>
        <h3>{{ title }}</h3>
        <p v-if="description" class="muted">{{ description }}</p>
        <slot />
    </div>
</template>
