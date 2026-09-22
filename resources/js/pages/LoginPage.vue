<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const form = ref({ email: '', password: '' });
const error = ref('');
const saving = ref(false);

async function submit() {
    error.value = '';
    saving.value = true;
    try {
        await auth.login(form.value);
        const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/beranda';
        await router.push(redirect.startsWith('/') ? redirect : '/beranda');
    } catch (err) {
        error.value = errorMessage(err, 'Email atau kata sandi tidak cocok.');
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
                <p class="eyebrow">Masuk</p>
                <h1>Selamat datang kembali</h1>
                <p class="muted">Lanjutkan agenda kebunmu dari tempat terakhir.</p>
                <form @submit.prevent="submit">
                    <p v-if="error" class="form-error">{{ error }}</p>
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input id="email" v-model="form.email" type="email" autocomplete="email" required>
                    </div>
                    <div class="form-field">
                        <label for="password">Kata sandi</label>
                        <input id="password" v-model="form.password" type="password" autocomplete="current-password" required>
                    </div>
                    <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Memeriksa...' : 'Masuk' }}</PrimaryButton>
                </form>
                <p class="auth-links">
                    <RouterLink to="/lupa-kata-sandi">Lupa kata sandi?</RouterLink>
                    <br>
                    Belum punya akun? <RouterLink to="/register">Daftar</RouterLink>
                </p>
            </section>
        </main>
    </div>
</template>
