<script setup>
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
</script>

<template>
    <div class="toast" :class="{ show: toast.visible }" role="status" aria-live="polite">
        <svg><use href="#i-check"></use></svg>
        <span>{{ toast.message }}</span>
    </div>
</template>
