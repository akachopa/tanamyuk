<script setup>
import { computed, onMounted, ref } from 'vue';
import AppShell from '@/components/layout/AppShell.vue';
import GardenCard from '@/components/garden/GardenCard.vue';
import CycleRecord from '@/components/garden/CycleRecord.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { demo } from '@/data/demo';
import * as gardensApi from '@/api/gardens';
import * as cyclesApi from '@/api/cycles';

const loading = ref(true);
const tab = ref('semua');
const gardens = ref([]);
const cycles = ref([]);

const tabs = [
    { id: 'semua', label: 'Semua' },
    { id: 'aktif', label: 'Aktif' },
    { id: 'panen', label: 'Segera panen' },
    { id: 'riwayat', label: 'Riwayat' },
];

const visibleCycles = computed(() => {
    if (tab.value === 'aktif') {
        return cycles.value.filter((cycle) => /aktif|active|tumbuh|bunga/i.test(`${cycle.subtitle} ${cycle.title}`));
    }
    if (tab.value === 'panen') {
        return cycles.value.filter((cycle) => /panen|72%|58%/i.test(cycle.subtitle));
    }
    if (tab.value === 'riwayat') {
        return cycles.value.filter((cycle) => /selesai|riwayat|done/i.test(`${cycle.subtitle} ${cycle.title}`));
    }
    return cycles.value;
});

function mapGarden(garden, cycleCount) {
    const crops = garden.crops || garden.commodities || [];
    return {
        id: garden.id,
        name: garden.name,
        location: [garden.location_label || garden.location_type, garden.status === 'active' ? 'Aktif' : garden.status].filter(Boolean).join(' • ') || 'Aktif',
        area: garden.area_m2 ? `${Number(garden.area_m2)} m²` : '—',
        sun: garden.sunlight_hours ? `${Number(garden.sunlight_hours)} jam` : '—',
        cycles: String(cycleCount),
        crops: Array.isArray(crops) ? crops.map((crop) => crop.name || crop) : [],
    };
}

function mapCycle(cycle) {
    return {
        id: cycle.id,
        title: cycle.name,
        subtitle: [cycle.quantity ? `${cycle.quantity} ${cycle.quantity_unit || ''}`.trim() : null, cycle.status].filter(Boolean).join(' • '),
        icon: 'i-leaf',
        tone: '',
    };
}

onMounted(async () => {
    try {
        const [gardenList, cycleList] = await Promise.all([
            gardensApi.list(),
            cyclesApi.list(),
        ]);
        cycles.value = cycleList.map(mapCycle);
        gardens.value = gardenList.map((garden) => mapGarden(
            garden,
            cycleList.filter((cycle) => cycle.garden_id === garden.id).length,
        ));
    } catch {
        gardens.value = demo.gardens.map((garden) => ({ ...garden }));
        cycles.value = demo.cycles.map((cycle) => ({ ...cycle }));
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <AppShell>
        <section class="page active">
            <div class="page-intro">
                <p class="eyebrow">Kebun saya</p>
                <h1>Ruang kecil, hasil nyata</h1>
                <p class="muted">Kelola lokasi dan semua siklus tanammu.</p>
                <RouterLink to="/kebun/baru" class="text-button">Tambah lahan</RouterLink>
            </div>
            <div class="tabs">
                <button
                    v-for="item in tabs"
                    :key="item.id"
                    class="tab"
                    :class="{ active: tab === item.id }"
                    type="button"
                    @click="tab = item.id"
                >
                    {{ item.label }}<template v-if="item.id === 'aktif'"> {{ cycles.length }}</template>
                </button>
            </div>
            <p v-if="loading" class="muted">Memuat lahan...</p>
            <div v-else-if="gardens.length" class="garden-list">
                <GardenCard v-for="garden in gardens" :key="garden.id" :garden="garden" :to="`/kebun/${garden.id}`" />
            </div>
            <EmptyState v-else icon="i-leaf" title="Belum ada lahan" description="Buat lahan pertama supaya tanaman punya tempat tumbuh." />
            <section class="section">
                <div class="section-head">
                    <h2>Siklus aktif</h2>
                </div>
                <div v-if="visibleCycles.length" class="record-list">
                    <CycleRecord v-for="cycle in visibleCycles" :key="cycle.id" :cycle="cycle" />
                </div>
                <EmptyState v-else icon="i-leaf" title="Tidak ada siklus di tab ini" description="Tambah tanaman untuk memulai siklus baru." />
            </section>
            <section class="section">
                <PrimaryButton to="/siklus/baru" wide>
                    <svg class="button-icon"><use href="#i-plus"></use></svg>
                    Tambah tanaman
                </PrimaryButton>
            </section>
        </section>
    </AppShell>
</template>
