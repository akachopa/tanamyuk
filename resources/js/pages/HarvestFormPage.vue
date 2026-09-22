<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import { demo } from '@/data/demo';
import { useToastStore } from '@/stores/toast';
import * as cyclesApi from '@/api/cycles';
import * as harvestsApi from '@/api/harvests';

const router = useRouter();
const toast = useToastStore();
const saving = ref(false);
const error = ref('');
const cycles = ref([]);
const form = ref({
    planting_cycle_id: '',
    harvest_date: new Date().toISOString().slice(0, 10),
    quantity: 1,
    unit: 'kg',
    usage_type: 'konsumsi',
    notes: '',
});

onMounted(async () => {
    try {
        cycles.value = await cyclesApi.list();
    } catch {
        cycles.value = demo.cycles.map((cycle) => ({ id: cycle.id, name: cycle.title }));
    }
    form.value.planting_cycle_id = cycles.value[0]?.id || '';
});

async function submit() {
    error.value = '';
    saving.value = true;
    try {
        await harvestsApi.create({ ...form.value, quantity: Number(form.value.quantity) });
        toast.show('Panen tercatat');
        await router.push('/catatan');
    } catch (err) {
        if (!err?.response) {
            toast.show('Panen disimpan di perangkat');
            await router.push('/catatan');
        } else {
            error.value = errorMessage(err, 'Panen belum tersimpan.');
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <AppShell :show-fab="false">
        <section class="page active">
            <div class="page-intro">
                <button class="text-button" type="button" @click="router.back()">Kembali</button>
                <h1>Catat panen</h1>
                <p class="muted">Jumlah, satuan, dan untuk apa hasilnya dipakai.</p>
            </div>
            <form class="auth-card" @submit.prevent="submit">
                <p v-if="error" class="form-error">{{ error }}</p>
                <div class="form-field">
                    <label for="planting_cycle_id">Siklus</label>
                    <select id="planting_cycle_id" v-model="form.planting_cycle_id" required>
                        <option v-for="cycle in cycles" :key="cycle.id" :value="cycle.id">{{ cycle.name || cycle.title }}</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="harvest_date">Tanggal panen</label>
                    <input id="harvest_date" v-model="form.harvest_date" type="date" required>
                </div>
                <div class="form-field">
                    <label for="quantity">Jumlah</label>
                    <input id="quantity" v-model="form.quantity" type="number" min="0" step="0.1" required>
                </div>
                <div class="form-field">
                    <label for="unit">Satuan</label>
                    <select id="unit" v-model="form.unit">
                        <option value="kg">kg</option>
                        <option value="gram">gram</option>
                        <option value="ikat">ikat</option>
                        <option value="buah">buah</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="usage_type">Pemanfaatan</label>
                    <select id="usage_type" v-model="form.usage_type">
                        <option value="konsumsi">Dikonsumsi keluarga</option>
                        <option value="dibagikan">Dibagikan</option>
                        <option value="dijual">Dijual</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" v-model="form.notes" />
                </div>
                <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan panen' }}</PrimaryButton>
            </form>
        </section>
    </AppShell>
</template>
