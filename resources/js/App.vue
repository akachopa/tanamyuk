<script setup>
import { onMounted, onUnmounted } from 'vue';
import { RouterView } from 'vue-router';
import IconSprite from '@/components/icons/IconSprite.vue';
import Toast from '@/components/ui/Toast.vue';
import { useSyncStore } from '@/stores/sync';

const sync = useSyncStore();

function onOnline() {
    sync.setOnline(true);
}

function onOffline() {
    sync.setOnline(false);
}

onMounted(() => {
    window.addEventListener('online', onOnline);
    window.addEventListener('offline', onOffline);
});

onUnmounted(() => {
    window.removeEventListener('online', onOnline);
    window.removeEventListener('offline', onOffline);
});
</script>

<template>
    <IconSprite />
    <RouterView />
    <Toast />
</template>
