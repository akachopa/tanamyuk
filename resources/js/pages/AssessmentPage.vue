<script setup>
import { computed, ref } from 'vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import * as assessmentsApi from '@/api/assessments';
import { mapApiRecommendations, recommendPlants } from '@/utils/recommend';

const steps = [
    {
        key: 'area_m2',
        title: 'Seberapa luas lahanmu?',
        options: [
            { label: 'Kurang dari 2 m²', value: 1 },
            { label: '2–5 m²', value: 4 },
            { label: '6–15 m²', value: 10 },
            { label: 'Lebih dari 15 m²', value: 20 },
        ],
    },
    {
        key: 'sunlight_hours',
        title: 'Berapa jam sinar matahari?',
        options: [
            { label: 'Kurang dari 3 jam', value: 2 },
            { label: '3–5 jam', value: 4 },
            { label: '6–8 jam', value: 7 },
            { label: 'Lebih dari 8 jam', value: 9 },
        ],
    },
    {
        key: 'water_source',
        title: 'Sumber air utama?',
        options: [
            { label: 'Keran', value: 'keran' },
            { label: 'Ember atau tandon', value: 'ember' },
            { label: 'Air hujan', value: 'hujan' },
            { label: 'Sumur', value: 'sumur' },
        ],
    },
    {
        key: 'care_minutes',
        title: 'Waktu perawatan per hari?',
        options: [
            { label: 'Di bawah 15 menit', value: 10 },
            { label: '15–30 menit', value: 20 },
            { label: '30–60 menit', value: 45 },
            { label: 'Lebih dari 1 jam', value: 75 },
        ],
    },
    {
        key: 'experience',
        title: 'Pengalaman menanam?',
        options: [
            { label: 'Baru mulai', value: 'baru' },
            { label: 'Pernah menanam', value: 'pernah' },
            { label: 'Sudah rutin', value: 'rutin' },
        ],
    },
    {
        key: 'goal',
        title: 'Tujuan utama berkebun?',
        options: [
            { label: 'Konsumsi keluarga', value: 'konsumsi' },
            { label: 'Hobi', value: 'hobi' },
            { label: 'Hemat belanja', value: 'hemat' },
            { label: 'Jualan kecil', value: 'jualan' },
        ],
    },
    {
        key: 'method',
        title: 'Metode yang kamu suka?',
        options: [
            { label: 'Polybag', value: 'polybag' },
            { label: 'Tanam tanah', value: 'tanah' },
            { label: 'Hidroponik', value: 'hidroponik' },
            { label: 'Vertikultur', value: 'vertikultur' },
        ],
    },
];

const step = ref(0);
const answers = ref({});
const results = ref([]);
const submitting = ref(false);
const done = ref(false);
const usedDemo = ref(false);

const current = computed(() => steps[step.value]);
const progress = computed(() => Math.round(((step.value + (done.value ? 1 : 0)) / steps.length) * 100));

function choose(value) {
    answers.value = { ...answers.value, [current.value.key]: value };
}

async function next() {
    if (answers.value[current.value.key] === undefined) {
        return;
    }
    if (step.value < steps.length - 1) {
        step.value += 1;
        return;
    }
    submitting.value = true;
    try {
        const payload = await assessmentsApi.submit({ answers: answers.value });
        const mapped = mapApiRecommendations(payload);
        results.value = mapped.length ? mapped : recommendPlants(answers.value);
        usedDemo.value = mapped.length === 0;
    } catch {
        results.value = recommendPlants(answers.value);
        usedDemo.value = true;
    } finally {
        submitting.value = false;
        done.value = true;
    }
}

function back() {
    if (done.value) {
        done.value = false;
        return;
    }
    if (step.value > 0) {
        step.value -= 1;
    }
}
</script>

<template>
    <div>
        <header class="topbar">
            <div class="topbar-row">
                <RouterLink to="/" class="brand">
                    <div class="brand-mark">
                        <svg><use href="#i-leaf"></use></svg>
                    </div>
                    <p class="brand-name">Tanam<span>Yuk</span></p>
                </RouterLink>
                <RouterLink to="/login" class="text-button">Masuk</RouterLink>
            </div>
        </header>
        <main>
            <section class="page active">
                <div class="page-intro">
                    <p class="eyebrow">Asesmen lahan</p>
                    <h1>{{ done ? 'Tanaman yang cocok' : 'Cek tanaman yang cocok' }}</h1>
                    <p class="muted">{{ done ? 'Skor disusun dari luas, cahaya, air, waktu, dan pengalamanmu.' : 'Jawab singkat. Tidak perlu istilah yang rumit.' }}</p>
                </div>
                <div class="wizard-progress">
                    <div class="progress-top">
                        <span>{{ done ? 'Selesai' : `Langkah ${step + 1} dari ${steps.length}` }}</span>
                        <strong>{{ progress }}%</strong>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" :style="{ width: `${progress}%` }" />
                    </div>
                </div>

                <template v-if="!done">
                    <h2>{{ current.title }}</h2>
                    <div class="quick-grid">
                        <button
                            v-for="option in current.options"
                            :key="option.label"
                            class="quick-action"
                            :class="{ active: answers[current.key] === option.value }"
                            type="button"
                            :aria-pressed="answers[current.key] === option.value"
                            @click="choose(option.value)"
                        >
                            <strong>{{ option.label }}</strong>
                        </button>
                    </div>
                    <div class="form-actions section">
                        <PrimaryButton wide :disabled="answers[current.key] === undefined || submitting" @click="next">
                            {{ step === steps.length - 1 ? (submitting ? 'Menyusun...' : 'Lihat rekomendasi') : 'Lanjut' }}
                        </PrimaryButton>
                        <button v-if="step > 0" class="outline-button wide" type="button" @click="back">Kembali</button>
                    </div>
                </template>

                <template v-else>
                    <p v-if="usedDemo" class="muted">Menampilkan perkiraan di perangkat karena server belum menjawab.</p>
                    <div class="record-list">
                        <article v-for="item in results" :key="item.name" class="record">
                            <div class="record-icon">
                                <svg><use href="#i-leaf"></use></svg>
                            </div>
                            <div>
                                <h3>{{ item.name }}</h3>
                                <p>{{ item.reasons?.[0] || item.method }}</p>
                                <div class="mini-progress"><span :style="{ width: `${item.score}%` }" /></div>
                            </div>
                            <span class="score-pill">{{ item.score }}</span>
                        </article>
                    </div>
                    <div class="form-actions section">
                        <PrimaryButton to="/register" wide>Daftar dan mulai tanam</PrimaryButton>
                        <button class="outline-button wide" type="button" @click="back">Ubah jawaban</button>
                    </div>
                </template>
            </section>
        </main>
    </div>
</template>
