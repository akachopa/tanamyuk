<script setup>
import { computed, onMounted, ref } from 'vue';
import AppShell from '@/components/layout/AppShell.vue';
import TaskItem from '@/components/home/TaskItem.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import { agendaTasks, demo } from '@/data/demo';
import { useToastStore } from '@/stores/toast';
import * as tasksApi from '@/api/tasks';

const toast = useToastStore();
const loading = ref(true);
const usingDemo = ref(false);
const filter = ref('all');
const tasks = ref([]);
const selected = ref('');

const days = computed(() => {
    const formatter = new Intl.DateTimeFormat('id-ID', { weekday: 'short', timeZone: 'Asia/Jakarta' });
    const dayFormatter = new Intl.DateTimeFormat('en-US', { day: 'numeric', timeZone: 'Asia/Jakarta' });
    const keyFormatter = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' });
    const start = new Date();
    start.setDate(start.getDate() - 2);
    return Array.from({ length: 5 }, (_, index) => {
        const date = new Date(start);
        date.setDate(start.getDate() + index);
        const key = keyFormatter.format(date);
        return {
            key,
            label: formatter.format(date).replace('.', ''),
            number: dayFormatter.format(date),
        };
    });
});

const todayKey = computed(() => new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Jakarta',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
}).format(new Date()));

const visibleTasks = computed(() => {
    return tasks.value.filter((task) => {
        if (usingDemo.value && selected.value !== todayKey.value) {
            return false;
        }
        if (!usingDemo.value && task.dateKey && task.dateKey !== selected.value) {
            return false;
        }
        if (filter.value === 'pending') {
            return !task.done;
        }
        if (filter.value === 'done') {
            return task.done;
        }
        return true;
    });
});

const dayTasks = computed(() => tasks.value.filter((task) => {
    if (usingDemo.value && selected.value !== todayKey.value) {
        return false;
    }
    if (!usingDemo.value && task.dateKey && task.dateKey !== selected.value) {
        return false;
    }
    return true;
}));
const pendingCount = computed(() => dayTasks.value.filter((task) => !task.done).length);
const dayTotal = computed(() => dayTasks.value.length);
const minutes = computed(() => visibleTasks.value.reduce((sum, task) => sum + Number(task.minutes || 0), 0));

function mapTask(task) {
    const due = task.due_at || task.scheduled_at;
    const date = due ? new Date(due) : null;
    const valid = date && !Number.isNaN(date.getTime());
    const dateKey = valid
        ? new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' }).format(date)
        : todayKey.value;
    return {
        id: task.id,
        title: task.title,
        subtitle: task.instruction || task.task_type || 'Kegiatan kebun',
        hour: valid ? String(date.getHours()).padStart(2, '0') : '00',
        minute: valid ? String(date.getMinutes()).padStart(2, '0') : '00',
        late: task.status !== 'completed' && valid && date < new Date(),
        done: task.status === 'completed' || Boolean(task.completed_at),
        minutes: 10,
        dateKey,
    };
}

onMounted(async () => {
    selected.value = todayKey.value;
    try {
        const list = await tasksApi.list();
        tasks.value = list.map(mapTask);
    } catch {
        usingDemo.value = true;
        tasks.value = agendaTasks().map((task) => ({ ...task, dateKey: todayKey.value }));
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
                <p class="eyebrow">Agenda kebun</p>
                <h1>Apa yang perlu dilakukan?</h1>
                <p class="muted">Selesaikan sedikit demi sedikit, TanamYuk mencatat progresnya.</p>
            </div>
            <div class="date-strip">
                <button
                    v-for="day in days"
                    :key="day.key"
                    class="date-item"
                    :class="{ active: selected === day.key }"
                    type="button"
                    @click="selected = day.key"
                >
                    <span>{{ day.label }}</span>
                    <strong>{{ day.number }}</strong>
                </button>
            </div>
            <div class="tabs">
                <button class="tab" :class="{ active: filter === 'all' }" type="button" @click="filter = 'all'">Semua {{ dayTotal }}</button>
                <button class="tab" :class="{ active: filter === 'pending' }" type="button" @click="filter = 'pending'">Belum selesai {{ pendingCount }}</button>
                <button class="tab" :class="{ active: filter === 'done' }" type="button" @click="filter = 'done'">Selesai</button>
            </div>
            <div class="section-head">
                <h2>{{ selected === todayKey ? 'Hari ini' : 'Kegiatan' }}</h2>
                <span class="muted" style="font-size:12px">±{{ minutes }} menit</span>
            </div>
            <p v-if="loading" class="muted">Memuat agenda...</p>
            <div v-else-if="visibleTasks.length" class="task-list">
                <TaskItem v-for="task in visibleTasks" :key="task.id" :task="task" @toggle="toggleTask" />
            </div>
            <EmptyState v-else icon="i-calendar" title="Tidak ada kegiatan" description="Pilih tanggal lain atau tandai tugas yang sudah dikerjakan." />
        </section>
    </AppShell>
</template>
