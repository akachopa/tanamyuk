<script setup>
defineProps({
    title: { type: String, default: 'Tips hari ini' },
    body: { type: String, default: '' },
});
</script>

<template>
    <article class="tip">
        <div class="tip-icon">
            <svg><use href="#i-bulb"></use></svg>
        </div>
        <div>
            <h3>{{ title }}</h3>
            <p>{{ body }}</p>
        </div>
        <svg class="chevron"><use href="#i-chevron"></use></svg>
    </article>
</template>
