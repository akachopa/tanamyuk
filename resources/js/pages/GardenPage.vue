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

const activeCycles = computed(() => cycles.value.filter((cycle) => cycle.status === 'active'));
const completedCycles = computed(() => cycles.value.filter((cycle) => cycle.status === 'completed' || cycle.status === 'archived'));

const visibleCycles = computed(() => {
    if (tab.value === 'aktif') {
        return activeCycles.value;
    }
    if (tab.value === 'panen') {
        return activeCycles.value.filter((cycle) => cycle.nearHarvest);
    }
    if (tab.value === 'riwayat') {
        return completedCycles.value;
    }
    return cycles.value;
});

function mapGarden(garden, activeCount, cropNames) {
    return {
        id: garden.id,
        name: garden.name,
        location: [garden.notes?.split('.')[0] || garden.location_type, garden.status === 'active' ? 'Aktif' : garden.status].filter(Boolean).join(' • ') || 'Aktif',
        area: garden.area_m2 ? `${Number(garden.area_m2)} m²` : '—',
        sun: garden.sunlight_hours ? `${Number(garden.sunlight_hours)} jam` : '—',
        cycles: String(activeCount),
        crops: cropNames,
    };
}

function mapCycle(cycle) {
    const start = cycle.start_date ? new Date(cycle.start_date) : null;
    const day = start && !Number.isNaN(start.getTime())
        ? Math.max(1, Math.round((Date.now() - start.getTime()) / 86400000) + 1)
        : null;
    const target = cycle.target_harvest_date ? new Date(cycle.target_harvest_date) : null;
    const daysToHarvest = target && !Number.isNaN(target.getTime())
        ? Math.ceil((target.getTime() - Date.now()) / 86400000)
        : null;
    const statusLabel = {
        active: 'Aktif',
        completed: 'Selesai',
        draft: 'Draft',
        paused: 'Dijeda',
        failed: 'Gagal',
        archived: 'Arsip',
    }[cycle.status] || cycle.status;

    return {
        id: cycle.id,
        garden_id: cycle.garden_id,
        commodity: cycle.commodity,
        name: cycle.name,
        title: cycle.name,
        status: cycle.status,
        nearHarvest: daysToHarvest !== null && daysToHarvest <= 14 && daysToHarvest >= 0,
        subtitle: [
            cycle.quantity ? `${Number(cycle.quantity)} ${cycle.quantity_unit || ''}`.trim() : null,
            day ? `Hari ke-${day}` : null,
            statusLabel,
        ].filter(Boolean).join(' • '),
        icon: cycle.status === 'completed' ? 'i-basket' : 'i-leaf',
        tone: cycle.status === 'completed' ? 'yellow' : '',
    };
}

onMounted(async () => {
    try {
        const [gardenList, cycleList] = await Promise.all([
            gardensApi.list(),
            cyclesApi.list(),
        ]);
        cycles.value = cycleList.map(mapCycle);
        gardens.value = gardenList.map((garden) => {
            const related = cycleList.filter((cycle) => cycle.garden_id === garden.id);
            const active = related.filter((cycle) => cycle.status === 'active');
            const cropNames = active.map((cycle) => cycle.commodity?.name || cycle.name);
            return mapGarden(garden, active.length, cropNames);
        });
    } catch {
        gardens.value = demo.gardens.map((garden) => ({ ...garden }));
        cycles.value = demo.cycles.map((cycle) => ({ ...cycle, status: 'active', nearHarvest: false }));
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
                    {{ item.label }}<template v-if="item.id === 'aktif'"> {{ activeCycles.length }}</template>
                </button>
            </div>
            <p v-if="loading" class="muted">Memuat lahan...</p>
            <div v-else-if="gardens.length" class="garden-list">
                <GardenCard v-for="garden in gardens" :key="garden.id" :garden="garden" :to="`/kebun/${garden.id}`" />
            </div>
            <EmptyState v-else icon="i-leaf" title="Belum ada lahan" description="Buat lahan pertama supaya tanaman punya tempat tumbuh." />
            <section class="section">
                <div class="section-head">
                    <h2>{{ tab === 'riwayat' ? 'Riwayat siklus' : (tab === 'panen' ? 'Segera panen' : (tab === 'semua' ? 'Semua siklus' : 'Siklus aktif')) }}</h2>
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
