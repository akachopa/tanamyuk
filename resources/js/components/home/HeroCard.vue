<script setup>
defineProps({
    garden: { type: String, default: 'Kebunmu' },
    headline: { type: String, default: 'Rawat sedikit, panen lebih dekat.' },
    weather: { type: String, default: '29°' },
    weatherLabel: { type: String, default: 'Cerah berawan' },
    done: { type: Number, default: 0 },
    total: { type: Number, default: 6 },
    meta: { type: String, default: '' },
});
</script>

<template>
    <article class="hero">
        <div class="hero-head">
            <div>
                <p class="hero-label">{{ garden }}</p>
                <h1>{{ headline }}</h1>
            </div>
            <div class="weather">
                <strong>{{ weather }}</strong>
                <span>{{ weatherLabel }}</span>
            </div>
        </div>
        <div class="hero-progress">
            <div class="progress-top">
                <span>Aktivitas minggu ini</span>
                <strong>{{ done }} dari {{ total }} selesai</strong>
            </div>
            <div class="progress-track">
                <div class="progress-fill" :style="{ width: `${total ? Math.round((done / total) * 100) : 0}%` }" />
            </div>
        </div>
        <div v-if="meta" class="hero-meta">
            <svg><use href="#i-leaf"></use></svg>
            <span>{{ meta }}</span>
        </div>
    </article>
</template>
