<script setup>
defineProps({
    stats: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="stat-grid">
        <article v-for="stat in stats" :key="stat.label" class="stat">
            <div class="stat-icon">
                <svg><use :href="`#${stat.icon}`"></use></svg>
            </div>
            <strong>{{ stat.value }}</strong>
            <span>{{ stat.label }}</span>
        </article>
    </div>
</template>
