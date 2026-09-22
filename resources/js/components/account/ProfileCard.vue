<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: 'Pengguna TanamYuk' },
    email: { type: String, default: '' },
});

const initials = computed(() => {
    const parts = props.name.trim().split(/\s+/).filter(Boolean);
    const letters = parts.slice(0, 2).map((part) => part[0]?.toUpperCase() || '');
    return letters.join('') || 'TY';
});
</script>

<template>
    <article class="profile-card">
        <div class="avatar">{{ initials }}</div>
        <div>
            <h2>{{ name }}</h2>
            <p>{{ email || 'Belum ada email' }}</p>
        </div>
        <svg class="chevron"><use href="#i-chevron"></use></svg>
    </article>
</template>
