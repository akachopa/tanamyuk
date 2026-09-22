<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import { useToastStore } from '@/stores/toast';
import * as plansApi from '@/api/plans';

const route = useRoute();
const toast = useToastStore();
const order = ref(null);
const channel = ref('qris');

const orderNumber = computed(() => route.params.orderNumber);
const amount = computed(() => order.value?.total_label || (String(orderNumber.value).includes('yearly') || String(orderNumber.value).includes('tahunan') ? 'Rp249.000' : 'Rp29.000'));
const vaNumber = computed(() => order.value?.va_number || '8800123456789012');

onMounted(async () => {
    try {
        const data = await plansApi.showOrder(orderNumber.value);
        order.value = {
            ...data,
            total_label: data.total ? `Rp${Math.round(Number(data.total)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}` : undefined,
            va_number: data.payment?.account_number || data.va_number,
        };
        if (data.payment?.channel) {
            channel.value = String(data.payment.channel).toLowerCase().includes('va') ? 'va' : 'qris';
        }
    } catch {
        order.value = null;
    }
});

async function copyVa() {
    try {
        await navigator.clipboard.writeText(vaNumber.value);
        toast.show('Nomor VA disalin');
    } catch {
        toast.show(vaNumber.value);
    }
}
</script>

<template>
    <AppShell :show-fab="false">
        <section class="page active">
            <div class="page-intro">
                <p class="eyebrow">Pembayaran</p>
                <h1>Selesaikan invoice</h1>
                <p class="muted">Pesanan {{ orderNumber }} • {{ amount }}</p>
            </div>
            <div class="tabs">
                <button class="tab" :class="{ active: channel === 'qris' }" type="button" @click="channel = 'qris'">QRIS</button>
                <button class="tab" :class="{ active: channel === 'va' }" type="button" @click="channel = 'va'">Virtual Account</button>
            </div>
            <article v-if="channel === 'qris'" class="plan-card">
                <div class="plan-top">
                    <div>
                        <h2>Pindai QRIS</h2>
                        <p>Bayar dari aplikasi bank atau dompet digital. Status diperbarui otomatis.</p>
                    </div>
                    <span class="plan-pill">Menunggu</span>
                </div>
                <div class="garden-facts" style="margin-top:16px">
                    <div class="garden-fact" style="background:rgba(255,255,255,.12);color:white">
                        <strong style="letter-spacing:2px">QR</strong>
                        <span style="color:#d7eadc">Kode menyusul dari kanal pembayaran</span>
                    </div>
                </div>
            </article>
            <article v-else class="garden-card">
                <div class="garden-body">
                    <p class="eyebrow">Nomor virtual account</p>
                    <h2>{{ vaNumber }}</h2>
                    <p class="muted">Transfer tepat {{ amount }} sebelum invoice kedaluwarsa.</p>
                    <button class="outline-button" type="button" @click="copyVa">Salin nomor</button>
                </div>
            </article>
            <section class="section">
                <div class="summary-card">
                    <div class="summary-item">
                        <span>Status</span>
                        <strong style="font-size:16px">{{ order?.status || 'pending' }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Total</span>
                        <strong style="font-size:16px">{{ amount }}</strong>
                    </div>
                </div>
            </section>
        </section>
    </AppShell>
</template>
