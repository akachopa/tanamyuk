<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { demoPlans } from '@/data/demo';
import { useToastStore } from '@/stores/toast';
import * as plansApi from '@/api/plans';

const router = useRouter();
const toast = useToastStore();
const plans = ref(demoPlans.map((plan) => ({ ...plan })));
const saving = ref('');

function rupiah(value) {
    const amount = Math.round(Number(value) || 0);
    return `Rp${amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
}

onMounted(async () => {
    try {
        const list = await plansApi.list();
        if (list.length) {
            plans.value = list.map((plan) => ({
                id: plan.id,
                code: plan.code,
                name: plan.name,
                price: Number(plan.price || 0),
                price_label: Number(plan.price) === 0 ? 'Rp0' : rupiah(plan.price),
                period: plan.billing_period === 'yearly' ? 'per tahun' : (plan.billing_period === 'monthly' ? 'per bulan' : 'selamanya'),
                description: plan.description || '',
                features: plan.features?.map((feature) => feature.name || feature) || [],
                featured: Boolean(plan.is_featured),
            }));
        }
    } catch {
        plans.value = demoPlans.map((plan) => ({ ...plan }));
    }
});

async function choose(plan) {
    if (plan.price === 0) {
        toast.show('Paket Gratis sudah bisa dipakai');
        await router.push('/beranda');
        return;
    }
    saving.value = plan.id;
    try {
        const order = await plansApi.createOrder({ plan_id: plan.id, channel: 'qris' });
        const number = order.order_number || order.number;
        await router.push(`/pembayaran/${number}`);
    } catch {
        await router.push(`/pembayaran/INV-${plan.code || 'DEMO'}`);
    } finally {
        saving.value = '';
    }
}
</script>

<template>
    <AppShell>
        <section class="page active">
            <div class="page-intro">
                <p class="eyebrow">Paket</p>
                <h1>Pilih cara menemani kebunmu</h1>
                <p class="muted">Gratis untuk mulai. Plus bila catatan dan pengingat ingin lebih lengkap.</p>
            </div>
            <div class="pricing-grid">
                <article v-for="plan in plans" :key="plan.id" class="feature-card" :class="{ featured: plan.featured }">
                    <p class="eyebrow">{{ plan.period }}</p>
                    <h3>{{ plan.name }}</h3>
                    <strong>{{ plan.price_label }}</strong>
                    <p>{{ plan.description }}</p>
                    <ul v-if="plan.features?.length">
                        <li v-for="feature in plan.features" :key="feature">{{ feature }}</li>
                    </ul>
                    <PrimaryButton wide :disabled="saving === plan.id" @click="choose(plan)">
                        {{ saving === plan.id ? 'Menyiapkan...' : 'Pilih paket' }}
                    </PrimaryButton>
                </article>
            </div>
        </section>
    </AppShell>
</template>
