<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';

const faqs = [
    {
        question: 'Apakah TanamYuk bisa digunakan tanpa internet?',
        answer: 'Bisa. Agenda, jurnal, biaya, panen, dan panduan yang sudah diunduh tetap dapat dibuka. Perubahan akan disinkronkan ketika koneksi tersedia.',
    },
    {
        question: 'Tanaman apa yang cocok untuk lahan sempit?',
        answer: 'Mulai dari asesmen singkat. TanamYuk mencocokkan luas, cahaya, air, dan waktu luangmu dengan tanaman seperti kangkung, pakcoy, atau cabai.',
    },
    {
        question: 'Bagaimana membayar paket Plus?',
        answer: 'Pilih paket, kemudian bayar lewat QRIS atau Virtual Account. Paket aktif setelah pembayaran terverifikasi.',
    },
];

const openFaq = ref(0);

const steps = [
    { title: 'Cek kondisi lahan', body: 'Jawab beberapa pertanyaan tentang luas, cahaya, air, dan waktu merawat.' },
    { title: 'Pilih tanaman yang cocok', body: 'Dapatkan rekomendasi beserta alasan, bukan daftar yang membingungkan.' },
    { title: 'Ikuti agenda harian', body: 'Siram, pupuk, dan catat panen sedikit demi sedikit dari ponsel.' },
];

const benefits = [
    { icon: 'i-clock', title: 'Pekerja', body: 'Agenda singkat yang muat di sela pulang kantor. Tidak perlu ingat jadwal di kepala.' },
    { icon: 'i-basket', title: 'Ibu rumah tangga', body: 'Dari dapur ke pekarangan: catat panen yang langsung dipakai keluarga.' },
    { icon: 'i-sun', title: 'Pensiunan', body: 'Rutinitas ringan, pengingat yang jelas, dan kebun yang tetap terawat.' },
];

const plans = [
    { name: 'Gratis', price: 'Rp0', period: 'Untuk mencoba', points: ['1 lahan', 'Agenda dasar', 'Rekomendasi tanaman'], featured: false },
    { name: 'Plus Bulanan', price: 'Rp29.000', period: 'per bulan', points: ['Lahan dan siklus lengkap', 'Catatan biaya & panen', 'Pengingat harian'], featured: false },
    { name: 'Plus Tahunan', price: 'Rp249.000', period: 'per tahun', points: ['Semua fitur Plus', 'Lebih hemat setahun', 'Bantuan prioritas'], featured: true },
];
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
                <div class="public-actions">
                    <RouterLink to="/login" class="text-button">Masuk</RouterLink>
                    <RouterLink to="/register" class="outline-button">Daftar</RouterLink>
                </div>
            </div>
        </header>
        <main>
            <section class="landing-hero">
                <p class="eyebrow">Kebun kecil, hasil nyata</p>
                <h1>Punya Sedikit Ruang? Tanam Yuk!</h1>
                <p>Pendamping menanam untuk pekarangan, balkon, dan halaman sempit. Mulai dari tanaman yang benar-benar cocok dengan waktumu.</p>
                <PrimaryButton to="/asesmen">Cek Tanaman yang Cocok</PrimaryButton>
            </section>

            <section class="section">
                <div class="section-head">
                    <h2>Tiga langkah mulai menanam</h2>
                </div>
                <ol class="step-list">
                    <li v-for="(step, index) in steps" :key="step.title" class="step-item">
                        <span class="step-index">{{ index + 1 }}</span>
                        <div>
                            <h3>{{ step.title }}</h3>
                            <p>{{ step.body }}</p>
                        </div>
                    </li>
                </ol>
            </section>

            <section class="section">
                <div class="section-head">
                    <h2>Cocok untuk ritmemu</h2>
                </div>
                <div class="feature-grid">
                    <article v-for="item in benefits" :key="item.title" class="feature-card">
                        <div class="feature-icon">
                            <svg><use :href="`#${item.icon}`"></use></svg>
                        </div>
                        <h3>{{ item.title }}</h3>
                        <p>{{ item.body }}</p>
                    </article>
                </div>
            </section>

            <section class="section">
                <div class="section-head">
                    <h2>Pilih paket</h2>
                </div>
                <div class="pricing-grid">
                    <article v-for="plan in plans" :key="plan.name" class="feature-card" :class="{ featured: plan.featured }">
                        <p class="eyebrow">{{ plan.period }}</p>
                        <h3>{{ plan.name }}</h3>
                        <strong>{{ plan.price }}</strong>
                        <ul>
                            <li v-for="point in plan.points" :key="point">{{ point }}</li>
                        </ul>
                        <PrimaryButton to="/register" wide>Mulai</PrimaryButton>
                    </article>
                </div>
            </section>

            <section class="section">
                <div class="section-head">
                    <h2>Pertanyaan yang sering muncul</h2>
                </div>
                <div class="faq-list">
                    <article v-for="(faq, index) in faqs" :key="faq.question" class="faq-item" :class="{ open: openFaq === index }">
                        <button class="faq-question" type="button" @click="openFaq = openFaq === index ? -1 : index">
                            <span>{{ faq.question }}</span>
                            <svg><use href="#i-down"></use></svg>
                        </button>
                        <div class="faq-answer">
                            <p>{{ faq.answer }}</p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="section">
                <article class="landing-hero">
                    <p class="eyebrow">Siap menanam</p>
                    <h1>Ruang sedikit tetap bisa panen.</h1>
                    <p>Buat akun gratis, cek tanaman yang cocok, lalu ikuti agenda hari ini.</p>
                    <PrimaryButton to="/register">Daftar TanamYuk</PrimaryButton>
                </article>
            </section>
        </main>
    </div>
</template>
