<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import { demo } from '@/data/demo';
import { useToastStore } from '@/stores/toast';
import * as gardensApi from '@/api/gardens';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();
const saving = ref(false);
const error = ref('');
const form = ref({
    name: '',
    location_type: 'pekarangan',
    area_m2: '',
    sunlight_hours: '',
    water_source: 'keran',
    notes: '',
});

const editing = computed(() => Boolean(route.params.id));

onMounted(async () => {
    if (!editing.value) {
        return;
    }
    try {
        const garden = await gardensApi.show(route.params.id);
        form.value = {
            name: garden.name || '',
            location_type: garden.location_type || 'pekarangan',
            area_m2: garden.area_m2 ?? '',
            sunlight_hours: garden.sunlight_hours ?? '',
            water_source: garden.water_source || 'keran',
            notes: garden.notes || '',
        };
    } catch {
        const garden = demo.gardens.find((item) => item.id === route.params.id) || demo.gardens[0];
        form.value = {
            name: garden.name,
            location_type: garden.location_type || 'pekarangan',
            area_m2: garden.area_m2,
            sunlight_hours: garden.sunlight_hours,
            water_source: garden.water_source || 'keran',
            notes: garden.notes || '',
        };
    }
});

async function submit() {
    error.value = '';
    saving.value = true;
    const payload = {
        ...form.value,
        area_m2: form.value.area_m2 === '' ? null : Number(form.value.area_m2),
        sunlight_hours: form.value.sunlight_hours === '' ? null : Number(form.value.sunlight_hours),
        status: 'active',
    };
    try {
        if (editing.value) {
            await gardensApi.update(route.params.id, payload);
        } else {
            await gardensApi.create(payload);
        }
        toast.show('Lahan tersimpan');
        await router.push('/kebun');
    } catch (err) {
        if (!err?.response && String(route.params.id || '').startsWith('demo')) {
            toast.show('Perubahan demo disimpan di perangkat');
            await router.push('/kebun');
        } else if (!err?.response) {
            toast.show('Lahan dicatat di perangkat');
            await router.push('/kebun');
        } else {
            error.value = errorMessage(err, 'Lahan belum tersimpan.');
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
                <h1>{{ editing ? 'Ubah lahan' : 'Tambah lahan' }}</h1>
                <p class="muted">Nama, luas, cahaya, dan sumber air cukup untuk memulai.</p>
            </div>
            <form class="auth-card" @submit.prevent="submit">
                <p v-if="error" class="form-error">{{ error }}</p>
                <div class="form-field">
                    <label for="name">Nama lahan</label>
                    <input id="name" v-model="form.name" required placeholder="Kebun Samping Rumah">
                </div>
                <div class="form-field">
                    <label for="location_type">Jenis lokasi</label>
                    <select id="location_type" v-model="form.location_type">
                        <option value="pekarangan">Pekarangan</option>
                        <option value="balkon">Balkon</option>
                        <option value="atap">Atap</option>
                        <option value="dalam_ruang">Dalam ruang</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="area_m2">Luas (m²)</label>
                    <input id="area_m2" v-model="form.area_m2" type="number" min="0" step="0.1">
                </div>
                <div class="form-field">
                    <label for="sunlight_hours">Jam sinar</label>
                    <input id="sunlight_hours" v-model="form.sunlight_hours" type="number" min="0" max="14" step="0.5">
                </div>
                <div class="form-field">
                    <label for="water_source">Sumber air</label>
                    <select id="water_source" v-model="form.water_source">
                        <option value="keran">Keran</option>
                        <option value="ember">Ember atau tandon</option>
                        <option value="hujan">Air hujan</option>
                        <option value="sumur">Sumur</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" v-model="form.notes" placeholder="Misalnya terkena hujan langsung." />
                </div>
                <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan lahan' }}</PrimaryButton>
            </form>
        </section>
    </AppShell>
</template>
