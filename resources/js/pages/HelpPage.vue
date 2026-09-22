<script setup>
import { computed, onMounted, ref } from 'vue';
import AppShell from '@/components/layout/AppShell.vue';
import { demoFaqs } from '@/data/demo';
import * as articlesApi from '@/api/articles';

const query = ref('');
const open = ref(0);
const items = ref(demoFaqs.map((item) => ({ ...item })));

const filtered = computed(() => {
    const needle = query.value.trim().toLowerCase();
    if (!needle) {
        return items.value;
    }
    return items.value.filter((item) => `${item.question} ${item.answer} ${item.keywords || ''}`.toLowerCase().includes(needle));
});

onMounted(async () => {
    try {
        const articles = await articlesApi.list();
        if (articles.length) {
            items.value = articles.map((article) => ({
                question: article.title,
                answer: article.excerpt || article.summary || article.body || '',
                keywords: article.keywords || '',
            }));
        }
    } catch {
        items.value = demoFaqs.map((item) => ({ ...item }));
    }
});
</script>

<template>
    <AppShell>
        <section class="page active">
            <div class="page-intro">
                <p class="eyebrow">Pusat bantuan</p>
                <h1>Pertanyaan yang sering ditanyakan</h1>
                <p class="muted">Cari penyiraman, pupuk, pembayaran, atau cara memakai aplikasi.</p>
            </div>
            <div class="faq-search">
                <svg><use href="#i-search"></use></svg>
                <input v-model="query" type="search" placeholder="Cari penyiraman, pupuk, pembayaran..." autocomplete="off">
            </div>
            <div v-if="filtered.length" class="faq-list">
                <article v-for="(item, index) in filtered" :key="item.question" class="faq-item" :class="{ open: open === index }">
                    <button class="faq-question" type="button" @click="open = open === index ? -1 : index">
                        <span>{{ item.question }}</span>
                        <svg><use href="#i-down"></use></svg>
                    </button>
                    <div class="faq-answer"><p>{{ item.answer }}</p></div>
                </article>
            </div>
            <p v-else class="muted">Tidak ada jawaban untuk pencarian itu.</p>
        </section>
    </AppShell>
</template>
