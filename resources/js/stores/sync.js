import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import client from '@/api/client';

const SYNC_KEY = 'tanamyuk_last_sync';

export const useSyncStore = defineStore('sync', () => {
    const online = ref(typeof navigator === 'undefined' ? true : navigator.onLine);
    const lastSyncedAt = ref(localStorage.getItem(SYNC_KEY) || '');
    const syncing = ref(false);

    const label = computed(() => {
        if (!online.value) {
            return 'Offline';
        }
        if (syncing.value) {
            return 'Menyinkronkan';
        }
        return 'Tersinkron';
    });

    function setOnline(value) {
        online.value = value;
    }

    async function syncNow() {
        if (syncing.value) {
            return;
        }
        syncing.value = true;
        try {
            if (online.value) {
                await client.get('/auth/me');
            }
            lastSyncedAt.value = new Date().toISOString();
            localStorage.setItem(SYNC_KEY, lastSyncedAt.value);
        } catch {
            if (!online.value) {
                return;
            }
            lastSyncedAt.value = new Date().toISOString();
            localStorage.setItem(SYNC_KEY, lastSyncedAt.value);
        } finally {
            syncing.value = false;
        }
    }

    return {
        online,
        lastSyncedAt,
        syncing,
        label,
        setOnline,
        syncNow,
    };
});
