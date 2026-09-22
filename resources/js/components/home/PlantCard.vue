<script setup>
defineProps({
    plant: { type: Object, required: true },
});
</script>

<template>
    <article class="plant-card">
        <div class="plant-visual">
            <span class="badge">{{ plant.badge }}</span>
            <svg><use href="#i-leaf"></use></svg>
        </div>
        <div class="plant-info">
            <div class="plant-title">
                <h3>{{ plant.name }}</h3>
                <span>{{ plant.day }}</span>
            </div>
            <div class="mini-progress"><span :style="{ width: `${plant.progress}%` }" /></div>
            <div class="plant-meta">
                <span>{{ plant.stage }}</span>
                <strong>{{ plant.harvest }}</strong>
            </div>
        </div>
    </article>
</template>
