<script setup>
defineProps({
    cycle: { type: Object, required: true },
});
</script>

<template>
    <article class="record">
        <div class="record-icon" :class="cycle.tone">
            <svg><use :href="`#${cycle.icon || 'i-leaf'}`"></use></svg>
        </div>
        <div>
            <h3>{{ cycle.title }}</h3>
            <p>{{ cycle.subtitle }}</p>
        </div>
        <svg class="chevron"><use href="#i-chevron"></use></svg>
    </article>
</template>
