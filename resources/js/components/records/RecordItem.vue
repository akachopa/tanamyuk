<script setup>
defineProps({
    record: { type: Object, required: true },
});
</script>

<template>
    <article class="record">
        <div class="record-icon" :class="record.tone">
            <svg><use :href="`#${record.icon || 'i-book'}`"></use></svg>
        </div>
        <div>
            <h3>{{ record.title }}</h3>
            <p>{{ record.subtitle }}</p>
        </div>
        <span v-if="record.amount" class="record-amount">{{ record.amount }}</span>
        <svg v-else class="chevron"><use href="#i-chevron"></use></svg>
    </article>
</template>
