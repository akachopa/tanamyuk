<script setup>
import { computed, onMounted, ref } from 'vue';
import AppShell from '@/components/layout/AppShell.vue';
import HeroCard from '@/components/home/HeroCard.vue';
import StatGrid from '@/components/home/StatGrid.vue';
import TaskItem from '@/components/home/TaskItem.vue';
import PlantCard from '@/components/home/PlantCard.vue';
import TipCard from '@/components/home/TipCard.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import { useGreeting } from '@/composables/useGreeting';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { demo, homeTasks } from '@/data/demo';
import * as gardensApi from '@/api/gardens';
import * as tasksApi from '@/api/tasks';
import * as cyclesApi from '@/api/cycles';
import * as harvestsApi from '@/api/harvests';

const auth = useAuthStore();
const toast = useToastStore();
const { greeting, dateLabel } = useGreeting();

const loading = ref(true);
const usingDemo = ref(false);
const hero = ref({ ...demo.hero });
const stats = ref(demo.stats.map((item) => ({ ...item })));
const tasks = ref([]);
const plants = ref([]);
const tip = ref({ ...demo.tip });

const firstName = computed(() => {
    const name = auth.user?.name || 'Robbi';
    return name.split(' ')[0];
});

const weekDone = computed(() => {
    if (!usingDemo.value) {
        return tasks.value.filter((task) => task.done).length;
    }
    const completedNow = homeTasks().filter((task) => task.done).length;
    return Math.min(demo.hero.weekTotal, demo.hero.weekDone + completedNow);
});

function mapTask(task) {
    const due = task.due_at || task.scheduled_at;
    const date = due ? new Date(due) : null;
    const valid = date && !Number.isNaN(date.getTime());
    const late = task.status !== 'completed' && !task.completed_at && valid && date < new Date();
    return {
        id: task.id,
        title: task.title,
        subtitle: task.instruction || task.task_type || 'Kegiatan kebun',
        hour: valid ? String(date.getHours()).padStart(2, '0') : '00',
        minute: valid ? String(date.getMinutes()).padStart(2, '0') : '00',
        late,
        done: task.status === 'completed' || Boolean(task.completed_at),
    };
}

function mapPlant(cycle) {
    const start = cycle.start_date ? new Date(cycle.start_date) : null;
    const day = start && !Number.isNaN(start.getTime())
        ? Math.max(1, Math.round((Date.now() - start.getTime()) / 86400000))
        : null;
    return {
        name: cycle.name || cycle.commodity?.name || 'Tanaman',
        day: day ? `Hari ke-${day}` : 'Siklus aktif',
        badge: cycle.status === 'active' ? 'Tumbuh baik' : (cycle.status || 'Aktif'),
        stage: cycle.stage?.name || cycle.current_stage?.name || 'Berjalan',
        harvest: cycle.target_harvest_date ? `Panen ${cycle.target_harvest_date}` : 'Menuju panen',
        progress: Number(cycle.progress ?? 40),
    };
}

function useDemoData() {
    usingDemo.value = true;
    hero.value = { ...demo.hero };
    stats.value = demo.stats.map((item) => ({ ...item }));
    tasks.value = homeTasks().map((task) => task);
    plants.value = demo.plants.map((plant) => ({ ...plant }));
    tip.value = { ...demo.tip };
}

onMounted(async () => {
    try {
        const [gardens, taskList, cycles, harvests] = await Promise.all([
            gardensApi.list(),
            tasksApi.list({ scope: 'today' }),
            cyclesApi.list({ status: 'active' }),
            harvestsApi.list(),
        ]);
        const garden = gardens[0];
        const harvestKg = harvests.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
        hero.value = {
            garden: garden?.name || 'Kebunmu',
            headline: 'Rawat sedikit, panen lebih dekat.',
            weather: '29°',
            weatherLabel: 'Cerah berawan',
            weekDone: 0,
            weekTotal: Math.max(taskList.length, 1),
            meta: `${cycles.length} tanaman aktif`,
        };
        stats.value = [
            { icon: 'i-leaf', value: String(cycles.length), label: 'Tanaman aktif' },
            { icon: 'i-calendar', value: String(taskList.filter((task) => task.status !== 'completed').length), label: 'Tugas hari ini' },
            { icon: 'i-basket', value: harvestKg.toLocaleString('id-ID'), label: 'Kg dipanen' },
        ];
        tasks.value = taskList.map(mapTask);
        plants.value = cycles.map(mapPlant);
    } catch {
        useDemoData();
    } finally {
        loading.value = false;
    }
});

async function toggleTask(task) {
    const next = !task.done;
    task.done = next;
    if (usingDemo.value) {
        const source = demo.tasks.find((item) => item.id === task.id);
        if (source) {
            source.done = next;
        }
        toast.show(next ? 'Kegiatan ditandai selesai' : 'Kegiatan dibuka kembali');
        return;
    }
    try {
        if (next) {
            await tasksApi.complete(task.id);
        } else {
            await tasksApi.reopen(task.id);
        }
        toast.show(next ? 'Kegiatan ditandai selesai' : 'Kegiatan dibuka kembali');
    } catch {
        task.done = !next;
        toast.show('Perubahan belum tersimpan');
    }
}
</script>

<template>
    <AppShell>
        <section class="page active">
            <div class="page-intro">
                <p class="eyebrow">{{ dateLabel }}</p>
                <h1>{{ greeting }}, {{ firstName }}</h1>
                <p class="muted">Kebunmu tumbuh baik. Ada {{ tasks.filter((task) => !task.done).length }} kegiatan hari ini.</p>
            </div>
            <p v-if="loading" class="muted">Memuat kebun...</p>
            <template v-else>
                <HeroCard
                    :garden="hero.garden"
                    :headline="hero.headline"
                    :weather="hero.weather"
                    :weather-label="hero.weatherLabel"
                    :done="usingDemo ? weekDone : tasks.filter((task) => task.done).length"
                    :total="usingDemo ? hero.weekTotal : Math.max(tasks.length, 1)"
                    :meta="hero.meta"
                />
                <StatGrid :stats="stats" />
                <section class="section">
                    <div class="section-head">
                        <h2>Perlu dilakukan</h2>
                        <RouterLink to="/agenda" class="text-button">Lihat agenda</RouterLink>
                    </div>
                    <div v-if="tasks.length" class="task-list">
                        <TaskItem v-for="task in tasks" :key="task.id" :task="task" @toggle="toggleTask" />
                    </div>
                    <EmptyState v-else icon="i-check" title="Tidak ada tugas hari ini" description="Kebunmu sedang tenang. Cek agenda untuk hari berikutnya." />
                </section>
                <section class="section">
                    <div class="section-head">
                        <h2>Tanaman aktif</h2>
                        <RouterLink to="/kebun" class="text-button">Semua tanaman</RouterLink>
                    </div>
                    <div v-if="plants.length" class="plant-scroll">
                        <PlantCard v-for="plant in plants" :key="plant.name" :plant="plant" />
                    </div>
                    <EmptyState v-else icon="i-leaf" title="Belum ada tanaman" description="Tambahkan siklus tanam pertama dari halaman kebun." />
                </section>
                <section class="section">
                    <TipCard :title="tip.title" :body="tip.body" />
                </section>
            </template>
        </section>
    </AppShell>
</template>
