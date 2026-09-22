<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import { demo } from '@/data/demo';
import { useToastStore } from '@/stores/toast';
import * as gardensApi from '@/api/gardens';
import * as cyclesApi from '@/api/cycles';
import * as journalsApi from '@/api/journals';

const router = useRouter();
const toast = useToastStore();
const saving = ref(false);
const error = ref('');
const gardens = ref([]);
const cycles = ref([]);
const form = ref({
    garden_id: '',
    planting_cycle_id: '',
    activity_type: 'penyiraman',
    entry_date: new Date().toISOString().slice(0, 10),
    plant_condition: 'baik',
    duration_minutes: 10,
    notes: '',
});

onMounted(async () => {
    try {
        const [gardenList, cycleList] = await Promise.all([gardensApi.list(), cyclesApi.list()]);
        gardens.value = gardenList;
        cycles.value = cycleList;
    } catch {
        gardens.value = demo.gardens;
        cycles.value = demo.cycles.map((cycle) => ({ id: cycle.id, name: cycle.title, garden_id: cycle.garden_id }));
    }
    form.value.garden_id = gardens.value[0]?.id || '';
});

async function submit() {
    error.value = '';
    saving.value = true;
    try {
        await journalsApi.create({
            ...form.value,
            planting_cycle_id: form.value.planting_cycle_id || null,
            duration_minutes: Number(form.value.duration_minutes),
        });
        toast.show('Kegiatan tercatat');
        await router.push('/catatan');
    } catch (err) {
        if (!err?.response) {
            toast.show('Kegiatan disimpan di perangkat');
            await router.push('/catatan');
        } else {
            error.value = errorMessage(err, 'Kegiatan belum tersimpan.');
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
                <h1>Catat kegiatan</h1>
                <p class="muted">Siram, pupuk, atau amati tanaman. Singkat saja.</p>
            </div>
            <form class="auth-card" @submit.prevent="submit">
                <p v-if="error" class="form-error">{{ error }}</p>
                <div class="form-field">
                    <label for="garden_id">Lahan</label>
                    <select id="garden_id" v-model="form.garden_id" required>
                        <option v-for="garden in gardens" :key="garden.id" :value="garden.id">{{ garden.name }}</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="planting_cycle_id">Siklus</label>
                    <select id="planting_cycle_id" v-model="form.planting_cycle_id">
                        <option value="">Semua tanaman</option>
                        <option v-for="cycle in cycles" :key="cycle.id" :value="cycle.id">{{ cycle.name || cycle.title }}</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="activity_type">Jenis kegiatan</label>
                    <select id="activity_type" v-model="form.activity_type">
                        <option value="penyiraman">Penyiraman</option>
                        <option value="pemupukan">Pemupukan</option>
                        <option value="pemangkasan">Pemangkasan</option>
                        <option value="pengamatan">Pengamatan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="entry_date">Tanggal</label>
                    <input id="entry_date" v-model="form.entry_date" type="date" required>
                </div>
                <div class="form-field">
                    <label for="plant_condition">Kondisi tanaman</label>
                    <select id="plant_condition" v-model="form.plant_condition">
                        <option value="baik">Baik</option>
                        <option value="perlu_perhatian">Perlu perhatian</option>
                        <option value="bermasalah">Bermasalah</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="duration_minutes">Durasi (menit)</label>
                    <input id="duration_minutes" v-model="form.duration_minutes" type="number" min="1">
                </div>
                <div class="form-field">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" v-model="form.notes" />
                </div>
                <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan kegiatan' }}</PrimaryButton>
            </form>
        </section>
    </AppShell>
</template>
