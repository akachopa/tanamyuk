<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import { demo } from '@/data/demo';
import { useToastStore } from '@/stores/toast';
import * as gardensApi from '@/api/gardens';
import * as expensesApi from '@/api/expenses';

const router = useRouter();
const toast = useToastStore();
const saving = ref(false);
const error = ref('');
const gardens = ref([]);
const form = ref({
    garden_id: '',
    expense_date: new Date().toISOString().slice(0, 10),
    description: '',
    quantity: 1,
    unit: 'pcs',
    total: '',
});

onMounted(async () => {
    try {
        gardens.value = await gardensApi.list();
    } catch {
        gardens.value = demo.gardens;
    }
    form.value.garden_id = gardens.value[0]?.id || '';
});

async function submit() {
    error.value = '';
    saving.value = true;
    try {
        await expensesApi.create({
            ...form.value,
            garden_id: form.value.garden_id || null,
            quantity: Number(form.value.quantity),
            total: Number(form.value.total),
        });
        toast.show('Biaya tercatat');
        await router.push('/catatan');
    } catch (err) {
        if (!err?.response) {
            toast.show('Biaya disimpan di perangkat');
            await router.push('/catatan');
        } else {
            error.value = errorMessage(err, 'Biaya belum tersimpan.');
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
                <h1>Catat biaya</h1>
                <p class="muted">Benih, nutrisi, atau media tanam. Simpan totalnya.</p>
            </div>
            <form class="auth-card" @submit.prevent="submit">
                <p v-if="error" class="form-error">{{ error }}</p>
                <div class="form-field">
                    <label for="garden_id">Lahan</label>
                    <select id="garden_id" v-model="form.garden_id">
                        <option value="">Tidak khusus</option>
                        <option v-for="garden in gardens" :key="garden.id" :value="garden.id">{{ garden.name }}</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="description">Deskripsi</label>
                    <input id="description" v-model="form.description" required placeholder="Beli nutrisi AB Mix">
                </div>
                <div class="form-field">
                    <label for="expense_date">Tanggal</label>
                    <input id="expense_date" v-model="form.expense_date" type="date" required>
                </div>
                <div class="form-field">
                    <label for="quantity">Jumlah</label>
                    <input id="quantity" v-model="form.quantity" type="number" min="0" step="0.1">
                </div>
                <div class="form-field">
                    <label for="unit">Satuan</label>
                    <input id="unit" v-model="form.unit">
                </div>
                <div class="form-field">
                    <label for="total">Total (Rp)</label>
                    <input id="total" v-model="form.total" type="number" min="0" required>
                </div>
                <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan biaya' }}</PrimaryButton>
            </form>
        </section>
    </AppShell>
</template>
