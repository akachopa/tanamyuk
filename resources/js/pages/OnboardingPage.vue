<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import * as gardensApi from '@/api/gardens';
import * as assessmentsApi from '@/api/assessments';
import { recommendPlants } from '@/utils/recommend';

const auth = useAuthStore();
const toast = useToastStore();
const router = useRouter();
const step = ref(0);
const saving = ref(false);
const form = ref({
    primary_goal: '',
    experience_level: '',
    daily_available_minutes: '',
    name: 'Kebun Samping Rumah',
    area_m2: 12,
    sunlight_hours: 6,
    water_source: 'keran',
});

const steps = [
    {
        key: 'primary_goal',
        title: 'Apa tujuan berkebunmu?',
        options: [
            { label: 'Konsumsi keluarga', value: 'konsumsi' },
            { label: 'Hobi', value: 'hobi' },
            { label: 'Hemat belanja', value: 'hemat' },
            { label: 'Jualan kecil', value: 'jualan' },
        ],
    },
    {
        key: 'experience_level',
        title: 'Seberapa berpengalaman kamu?',
        options: [
            { label: 'Baru mulai', value: 'baru' },
            { label: 'Pernah menanam', value: 'pernah' },
            { label: 'Sudah rutin', value: 'rutin' },
        ],
    },
    {
        key: 'daily_available_minutes',
        title: 'Berapa menit sehari untuk merawat?',
        options: [
            { label: '10 menit', value: 10 },
            { label: '20 menit', value: 20 },
            { label: '30 menit', value: 30 },
            { label: '60 menit', value: 60 },
        ],
    },
];

const current = computed(() => steps[step.value]);
const recommendations = computed(() => recommendPlants({
    area_m2: form.value.area_m2,
    sunlight_hours: form.value.sunlight_hours,
    care_minutes: form.value.daily_available_minutes || 20,
    experience: form.value.experience_level || 'baru',
    goal: form.value.primary_goal || 'konsumsi',
    method: 'polybag',
    water_source: form.value.water_source,
}));

function pick(value) {
    form.value[current.value.key] = value;
}

async function next() {
    if (step.value < 3 && !form.value[current.value?.key]) {
        return;
    }
    if (step.value < 4) {
        step.value += 1;
        return;
    }
    saving.value = true;
    try {
        await auth.updateProfile({
            primary_goal: form.value.primary_goal,
            experience_level: form.value.experience_level,
            daily_available_minutes: Number(form.value.daily_available_minutes),
        });
        await gardensApi.create({
            name: form.value.name,
            area_m2: Number(form.value.area_m2),
            sunlight_hours: Number(form.value.sunlight_hours),
            water_source: form.value.water_source,
            location_type: 'pekarangan',
            status: 'active',
        });
        await assessmentsApi.submit({
            answers: {
                area_m2: Number(form.value.area_m2),
                sunlight_hours: Number(form.value.sunlight_hours),
                water_source: form.value.water_source,
                care_minutes: Number(form.value.daily_available_minutes),
                experience: form.value.experience_level,
                goal: form.value.primary_goal,
                method: 'polybag',
            },
        });
        toast.show('Kebun pertamamu siap');
    } catch {
        auth.setUser({
            ...(auth.user || {}),
            primary_goal: form.value.primary_goal,
            experience_level: form.value.experience_level,
            daily_available_minutes: Number(form.value.daily_available_minutes),
        });
        toast.show('Disimpan di perangkat');
    } finally {
        auth.markOnboardingDone();
        saving.value = false;
        await router.push('/beranda');
    }
}
</script>

<template>
    <AppShell :show-fab="false">
        <section class="page active">
            <div class="page-intro">
                <p class="eyebrow">Mulai menanam</p>
                <h1>Kenalan dengan lahanmu</h1>
                <p class="muted">Langkah {{ Math.min(step + 1, 5) }} dari 5</p>
            </div>
            <div class="progress-track wizard-progress">
                <div class="progress-fill" :style="{ width: `${((step + 1) / 5) * 100}%` }" />
            </div>

            <template v-if="step < 3">
                <h2>{{ current.title }}</h2>
                <div class="quick-grid">
                    <button
                        v-for="option in current.options"
                        :key="option.label"
                        class="quick-action"
                        :class="{ active: form[current.key] === option.value }"
                        type="button"
                        @click="pick(option.value)"
                    >
                        <strong>{{ option.label }}</strong>
                    </button>
                </div>
                <div class="form-actions section">
                    <PrimaryButton wide :disabled="form[current.key] === ''" @click="next">Lanjut</PrimaryButton>
                </div>
            </template>

            <form v-else-if="step === 3" class="auth-card" @submit.prevent="next">
                <h2>Buat lahan</h2>
                <div class="form-field">
                    <label for="garden-name">Nama lahan</label>
                    <input id="garden-name" v-model="form.name" required>
                </div>
                <div class="form-field">
                    <label for="area">Luas (m²)</label>
                    <input id="area" v-model="form.area_m2" type="number" min="0.5" step="0.5" required>
                </div>
                <div class="form-field">
                    <label for="sun">Jam sinar matahari</label>
                    <input id="sun" v-model="form.sunlight_hours" type="number" min="0" max="14" step="0.5" required>
                </div>
                <div class="form-field">
                    <label for="water">Sumber air</label>
                    <select id="water" v-model="form.water_source">
                        <option value="keran">Keran</option>
                        <option value="ember">Ember atau tandon</option>
                        <option value="hujan">Air hujan</option>
                        <option value="sumur">Sumur</option>
                    </select>
                </div>
                <PrimaryButton type="submit" wide>Lihat rekomendasi</PrimaryButton>
            </form>

            <template v-else>
                <h2>Rekomendasi awal</h2>
                <div class="record-list">
                    <article v-for="item in recommendations.slice(0, 3)" :key="item.name" class="record">
                        <div class="record-icon"><svg><use href="#i-leaf"></use></svg></div>
                        <div>
                            <h3>{{ item.name }}</h3>
                            <p>{{ item.reasons[0] }}</p>
                        </div>
                        <span class="score-pill">{{ item.score }}</span>
                    </article>
                </div>
                <div class="form-actions section">
                    <PrimaryButton wide :disabled="saving" @click="next">{{ saving ? 'Menyimpan...' : 'Masuk ke beranda' }}</PrimaryButton>
                </div>
            </template>
        </section>
    </AppShell>
</template>
