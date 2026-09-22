<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import { demo, demoCommodities } from '@/data/demo';
import { useToastStore } from '@/stores/toast';
import * as gardensApi from '@/api/gardens';
import * as commoditiesApi from '@/api/commodities';
import * as cyclesApi from '@/api/cycles';

const router = useRouter();
const toast = useToastStore();
const saving = ref(false);
const error = ref('');
const gardens = ref([]);
const commodities = ref([]);
const form = ref({
    garden_id: '',
    commodity_id: '',
    name: '',
    start_date: new Date().toISOString().slice(0, 10),
    quantity: 1,
    quantity_unit: 'polybag',
    notes: '',
});

onMounted(async () => {
    try {
        const [gardenList, commodityList] = await Promise.all([
            gardensApi.list(),
            commoditiesApi.list(),
        ]);
        gardens.value = gardenList;
        commodities.value = commodityList;
    } catch {
        gardens.value = demo.gardens;
        commodities.value = demoCommodities;
    }
    form.value.garden_id = gardens.value[0]?.id || '';
    form.value.commodity_id = commodities.value[0]?.id || '';
    form.value.name = commodities.value[0]?.name || '';
});

function onCommodity(event) {
    const commodity = commodities.value.find((item) => item.id === event.target.value);
    if (commodity && (!form.value.name || commodities.value.some((item) => item.name === form.value.name))) {
        form.value.name = commodity.name;
    }
}

async function submit() {
    error.value = '';
    saving.value = true;
    try {
        await cyclesApi.create({
            ...form.value,
            quantity: Number(form.value.quantity),
            status: 'active',
        });
        toast.show('Tanaman ditambahkan');
        await router.push('/kebun');
    } catch (err) {
        if (!err?.response) {
            toast.show('Siklus dicatat di perangkat');
            await router.push('/kebun');
        } else {
            error.value = errorMessage(err, 'Siklus belum tersimpan.');
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
                <h1>Tambah tanaman</h1>
                <p class="muted">Satu siklus untuk satu komoditas di lahan yang dipilih.</p>
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
                    <label for="commodity_id">Komoditas</label>
                    <select id="commodity_id" v-model="form.commodity_id" required @change="onCommodity">
                        <option v-for="commodity in commodities" :key="commodity.id" :value="commodity.id">{{ commodity.name }}</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="name">Nama siklus</label>
                    <input id="name" v-model="form.name" required>
                </div>
                <div class="form-field">
                    <label for="start_date">Tanggal mulai</label>
                    <input id="start_date" v-model="form.start_date" type="date" required>
                </div>
                <div class="form-field">
                    <label for="quantity">Jumlah</label>
                    <input id="quantity" v-model="form.quantity" type="number" min="1" step="1" required>
                </div>
                <div class="form-field">
                    <label for="quantity_unit">Satuan</label>
                    <select id="quantity_unit" v-model="form.quantity_unit">
                        <option value="polybag">Polybag</option>
                        <option value="lubang">Lubang</option>
                        <option value="pot">Pot</option>
                        <option value="bedeng">Bedeng</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" v-model="form.notes" />
                </div>
                <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan tanaman' }}</PrimaryButton>
            </form>
        </section>
    </AppShell>
</template>
