<script setup>
import { computed, onMounted, ref } from 'vue';
import AppShell from '@/components/layout/AppShell.vue';
import SummaryCard from '@/components/records/SummaryCard.vue';
import RecordItem from '@/components/records/RecordItem.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import { demo } from '@/data/demo';
import * as journalsApi from '@/api/journals';
import * as harvestsApi from '@/api/harvests';
import * as expensesApi from '@/api/expenses';
import * as issuesApi from '@/api/issues';

const loading = ref(true);
const tab = ref('semua');
const records = ref([]);
const summary = ref({ ...demo.summary });

const tabs = [
    { id: 'semua', label: 'Semua' },
    { id: 'journal', label: 'Kegiatan' },
    { id: 'expense', label: 'Biaya' },
    { id: 'harvest', label: 'Panen' },
    { id: 'issue', label: 'Masalah' },
];

const visible = computed(() => (tab.value === 'semua' ? records.value : records.value.filter((record) => record.type === tab.value)));

function rupiah(value) {
    const amount = Math.round(Number(value) || 0);
    return `Rp${amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
}

onMounted(async () => {
    try {
        const [journals, harvests, expenses, issues] = await Promise.all([
            journalsApi.list(),
            harvestsApi.list(),
            expensesApi.list(),
            issuesApi.list(),
        ]);
        const expenseTotal = expenses.reduce((sum, item) => sum + Number(item.total || 0), 0);
        const harvestTotal = harvests.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
        summary.value = {
            expense: rupiah(expenseTotal),
            harvest: `${harvestTotal.toLocaleString('id-ID')} kg`,
        };
        records.value = [
            ...journals.map((item) => ({
                id: item.id,
                type: 'journal',
                title: item.activity_type || 'Kegiatan',
                subtitle: item.entry_date || item.notes || '',
                amount: item.duration_minutes ? `${item.duration_minutes} mnt` : '',
                icon: 'i-camera',
                tone: '',
            })),
            ...harvests.map((item) => ({
                id: item.id,
                type: 'harvest',
                title: 'Panen',
                subtitle: item.harvest_date || '',
                amount: `${item.quantity} ${item.unit || ''}`.trim(),
                icon: 'i-basket',
                tone: 'yellow',
            })),
            ...expenses.map((item) => ({
                id: item.id,
                type: 'expense',
                title: item.description || 'Biaya',
                subtitle: item.expense_date || '',
                amount: rupiah(item.total),
                icon: 'i-coin',
                tone: 'orange',
            })),
            ...issues.map((item) => ({
                id: item.id,
                type: 'issue',
                title: item.symptom_category || 'Masalah tanaman',
                subtitle: item.status || item.description || '',
                amount: '',
                icon: 'i-warning',
                tone: 'orange',
            })),
        ];
    } catch {
        records.value = demo.records.map((record) => ({ ...record }));
        summary.value = { ...demo.summary };
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <AppShell>
        <section class="page active">
            <div class="page-intro">
                <p class="eyebrow">Catatan kebun</p>
                <h1>Lihat perjalanan tanammu</h1>
                <p class="muted">Kegiatan, biaya, masalah, dan panen tersimpan di sini.</p>
            </div>
            <SummaryCard :expense="summary.expense" :harvest="summary.harvest" />
            <div class="tabs">
                <button
                    v-for="item in tabs"
                    :key="item.id"
                    class="tab"
                    :class="{ active: tab === item.id }"
                    type="button"
                    @click="tab = item.id"
                >
                    {{ item.label }}
                </button>
            </div>
            <p v-if="loading" class="muted">Memuat catatan...</p>
            <div v-else-if="visible.length" class="record-list">
                <RecordItem v-for="record in visible" :key="record.id" :record="record" />
            </div>
            <EmptyState v-else icon="i-book" title="Belum ada catatan" description="Gunakan tombol tambah untuk mencatat kegiatan, panen, biaya, atau masalah." />
        </section>
    </AppShell>
</template>
