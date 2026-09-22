<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import TopBar from '@/components/layout/TopBar.vue';
import BottomNav from '@/components/layout/BottomNav.vue';

defineProps({
    showFab: { type: Boolean, default: true },
});

const router = useRouter();
const sheetOpen = ref(false);

const actions = [
    { label: 'Kegiatan', icon: 'i-check', tone: '', to: '/catatan/jurnal/baru' },
    { label: 'Hasil panen', icon: 'i-basket', tone: 'yellow', to: '/catatan/panen/baru' },
    { label: 'Biaya', icon: 'i-coin', tone: 'orange', to: '/catatan/biaya/baru' },
    { label: 'Masalah tanaman', icon: 'i-warning', tone: 'orange', to: '/catatan/masalah/baru' },
];

function openSheet() {
    sheetOpen.value = true;
}

function closeSheet() {
    sheetOpen.value = false;
}

function go(path) {
    sheetOpen.value = false;
    router.push(path);
}
</script>

<template>
    <div class="app">
        <TopBar />
        <main>
            <slot />
        </main>
        <button v-if="showFab" class="fab" type="button" aria-label="Tambah catatan" @click="openSheet">
            <svg><use href="#i-plus"></use></svg>
        </button>
        <BottomNav />
    </div>
    <div class="scrim" :class="{ open: sheetOpen }" role="dialog" aria-modal="true" aria-labelledby="quickTitle" @click.self="closeSheet">
        <div class="sheet">
            <div class="sheet-handle" />
            <div class="sheet-head">
                <div>
                    <p class="eyebrow">Catat cepat</p>
                    <h2 id="quickTitle">Apa yang ingin dicatat?</h2>
                </div>
                <button class="icon-button" type="button" aria-label="Tutup" @click="closeSheet">
                    <svg><use href="#i-x"></use></svg>
                </button>
            </div>
            <div class="quick-grid">
                <button v-for="action in actions" :key="action.to" class="quick-action" type="button" @click="go(action.to)">
                    <span class="record-icon" :class="action.tone">
                        <svg><use :href="`#${action.icon}`"></use></svg>
                    </span>
                    <strong>{{ action.label }}</strong>
                </button>
            </div>
        </div>
    </div>
</template>
