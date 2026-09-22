<script setup>
defineProps({
    garden: { type: Object, required: true },
    to: { type: String, default: '' },
});
</script>

<template>
    <component :is="to ? 'router-link' : 'article'" class="garden-card" :to="to || undefined">
        <div class="garden-banner">
            <h3>{{ garden.name }}</h3>
            <p>{{ garden.location }}</p>
        </div>
        <div class="garden-body">
            <div class="garden-facts">
                <div class="garden-fact"><strong>{{ garden.area }}</strong><span>Luas</span></div>
                <div class="garden-fact"><strong>{{ garden.sun }}</strong><span>Cahaya</span></div>
                <div class="garden-fact"><strong>{{ garden.cycles }}</strong><span>Siklus aktif</span></div>
            </div>
            <div v-if="garden.crops?.length" class="garden-crops">
                <div v-for="crop in garden.crops.slice(0, 3)" :key="crop" class="crop-dot">
                    <svg><use href="#i-leaf"></use></svg>
                </div>
                <span>{{ garden.crops.join(', ') }}</span>
            </div>
        </div>
    </component>
</template>
