<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import { demo } from '@/data/demo';
import { useToastStore } from '@/stores/toast';
import * as cyclesApi from '@/api/cycles';
import * as issuesApi from '@/api/issues';

const router = useRouter();
const toast = useToastStore();
const saving = ref(false);
const error = ref('');
const cycles = ref([]);
const form = ref({
    planting_cycle_id: '',
    symptom_category: 'daun',
    affected_part: 'daun',
    severity: 'sedang',
    description: '',
    discovered_at: new Date().toISOString().slice(0, 16),
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
        await issuesApi.create({
            ...form.value,
            discovered_at: form.value.discovered_at ? new Date(form.value.discovered_at).toISOString() : null,
            status: 'open',
        });
        toast.show('Masalah tercatat');
        await router.push('/catatan');
    } catch (err) {
        if (!err?.response) {
            toast.show('Masalah disimpan di perangkat');
            await router.push('/catatan');
        } else {
            error.value = errorMessage(err, 'Masalah belum tersimpan.');
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
                <h1>Catat masalah tanaman</h1>
                <p class="muted">Gejala yang kamu lihat hari ini, supaya mudah dipantau.</p>
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
                    <label for="symptom_category">Gejala</label>
                    <select id="symptom_category" v-model="form.symptom_category">
                        <option value="daun">Daun menguning</option>
                        <option value="hama">Hama</option>
                        <option value="layu">Layu</option>
                        <option value="busuk">Busuk</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="affected_part">Bagian tanaman</label>
                    <select id="affected_part" v-model="form.affected_part">
                        <option value="daun">Daun</option>
                        <option value="batang">Batang</option>
                        <option value="akar">Akar</option>
                        <option value="buah">Buah</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="severity">Keparahan</label>
                    <select id="severity" v-model="form.severity">
                        <option value="ringan">Ringan</option>
                        <option value="sedang">Sedang</option>
                        <option value="berat">Berat</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="discovered_at">Ditemukan</label>
                    <input id="discovered_at" v-model="form.discovered_at" type="datetime-local">
                </div>
                <div class="form-field">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" v-model="form.description" required />
                </div>
                <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan masalah' }}</PrimaryButton>
            </form>
        </section>
    </AppShell>
</template>
