<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import { errorMessage } from '@/api/client';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const form = ref({ name: '', email: '', password: '', password_confirmation: '' });
const error = ref('');
const saving = ref(false);

async function submit() {
    error.value = '';
    if (form.value.password !== form.value.password_confirmation) {
        error.value = 'Konfirmasi kata sandi belum sama.';
        return;
    }
    saving.value = true;
    try {
        await auth.register(form.value);
        await router.push('/onboarding');
    } catch (err) {
        error.value = errorMessage(err, 'Pendaftaran belum berhasil.');
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
                <p class="eyebrow">Daftar</p>
                <h1>Mulai kebun kecilmu</h1>
                <p class="muted">Gratis untuk lahan pertama. Kamu bisa menyesuaikan tanaman nanti.</p>
                <form @submit.prevent="submit">
                    <p v-if="error" class="form-error">{{ error }}</p>
                    <div class="form-field">
                        <label for="name">Nama</label>
                        <input id="name" v-model="form.name" type="text" autocomplete="name" required>
                    </div>
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input id="email" v-model="form.email" type="email" autocomplete="email" required>
                    </div>
                    <div class="form-field">
                        <label for="password">Kata sandi</label>
                        <input id="password" v-model="form.password" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                    <div class="form-field">
                        <label for="password_confirmation">Ulangi kata sandi</label>
                        <input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" required>
                    </div>
                    <PrimaryButton type="submit" wide :disabled="saving">{{ saving ? 'Membuat akun...' : 'Daftar' }}</PrimaryButton>
                </form>
                <p class="auth-links">Sudah punya akun? <RouterLink to="/login">Masuk</RouterLink></p>
            </section>
        </main>
    </div>
</template>
