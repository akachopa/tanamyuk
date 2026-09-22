<script setup>
import { useSyncStore } from '@/stores/sync';
import { useToastStore } from '@/stores/toast';

const sync = useSyncStore();
const toast = useToastStore();

async function onSync() {
    await sync.syncNow();
    toast.show(sync.online ? 'Data tersinkron' : 'Perangkat sedang offline');
}
</script>

<template>
    <header class="topbar">
        <div class="topbar-row">
            <RouterLink to="/beranda" class="brand">
                <div class="brand-mark">
                    <svg><use href="#i-leaf"></use></svg>
                </div>
                <p class="brand-name">Tanam<span>Yuk</span></p>
            </RouterLink>
            <button
                class="sync-button"
                :class="{ offline: !sync.online }"
                type="button"
                aria-label="Status koneksi"
                @click="onSync"
            >
                <span class="status-dot" />
                <span>{{ sync.label }}</span>
            </button>
        </div>
    </header>
</template>
