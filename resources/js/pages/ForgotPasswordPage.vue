<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import * as authApi from '@/api/auth';

const email = ref('');
const error = ref('');
const sent = ref(false);
const saving = ref(false);

async function submit() {
    error.value = '';
    saving.value = true;
    try {
        await authApi.forgotPassword({ email: email.value });
        sent.value = true;
    } catch (err) {
        error.value = errorMessage(err, 'Permintaan belum terkirim.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <header class="topbar">
            <div class="topbar-row">
                <RouterLink to="/" class="brand">
                    <div class="brand-mark"><svg><use href="#i-leaf"></use></svg></div>
                    <p class="brand-name">Tanam<span>Yuk</span></p>
                </RouterLink>
            </div>
        </header>
        <main>
            <section class="auth-card">
                <p class="eyebrow">Kata sandi</p>
                <h1>Pulihkan akses akun</h1>
                <p class="muted">Kami kirim tautan reset ke emailmu jika akun ditemukan.</p>
                <p v-if="sent" class="muted">Jika email terdaftar, periksa kotak masuk untuk langkah berikutnya.</p>
                <form v-else @submit.prevent="submit">
                    <p v-if="error" class="form-error">{{ error }}</p>
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input id="email" v-model="email" type="email" autocomplete="email" required>
                    </div>
                    <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Mengirim...' : 'Kirim tautan' }}</PrimaryButton>
                </form>
                <p class="auth-links"><RouterLink to="/login">Kembali masuk</RouterLink></p>
            </section>
        </main>
    </div>
</template>
