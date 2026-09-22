<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import ProfileCard from '@/components/account/ProfileCard.vue';
import SettingsGroup from '@/components/account/SettingsGroup.vue';
import { useAuthStore } from '@/stores/auth';
import { useSyncStore } from '@/stores/sync';
import { useToastStore } from '@/stores/toast';

const auth = useAuthStore();
const sync = useSyncStore();
const toast = useToastStore();
const router = useRouter();
const reminders = ref(localStorage.getItem('tanamyuk_reminders') !== '0');

const name = computed(() => auth.user?.name || 'Robbi Sugara');
const email = computed(() => auth.user?.email || 'robbi@tanamyuk.com');

const syncLabel = computed(() => {
    if (!sync.lastSyncedAt) {
        return sync.online ? 'Siap disinkronkan' : 'Menunggu koneksi';
    }
    return `Terakhir ${new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(new Date(sync.lastSyncedAt))}`;
});

const appItems = computed(() => [
    {
        title: 'Pengingat kegiatan',
        subtitle: reminders.value ? 'Aktif setiap hari pukul 06.30' : 'Pengingat dimatikan',
        icon: 'i-bell',
        toggle: true,
        enabled: reminders.value,
        action: 'reminders',
    },
    {
        title: 'Data offline',
        subtitle: syncLabel.value,
        icon: 'i-download',
        action: 'sync',
    },
]);

const helpItems = [
    { title: 'FAQ dan pusat bantuan', subtitle: 'Cari jawaban seputar budidaya dan aplikasi', icon: 'i-help', to: '/bantuan' },
    { title: 'Riwayat pembayaran', subtitle: 'QRIS dan Virtual Account', icon: 'i-card', to: '/paket' },
    { title: 'Privasi dan keamanan', subtitle: 'Kata sandi, perangkat, dan data pribadi', icon: 'i-shield', action: 'privacy' },
    { title: 'Keluar akun', subtitle: 'Sesi di perangkat ini akan diakhiri', icon: 'i-logout', danger: true, action: 'logout' },
];

async function onSelect(item) {
    if (item.action === 'reminders') {
        reminders.value = !reminders.value;
        localStorage.setItem('tanamyuk_reminders', reminders.value ? '1' : '0');
        toast.show(reminders.value ? 'Pengingat diaktifkan' : 'Pengingat dimatikan');
    }
    if (item.action === 'sync') {
        await sync.syncNow();
        toast.show(sync.online ? 'Data tersinkron' : 'Perangkat sedang offline');
    }
    if (item.action === 'privacy') {
        toast.show('Pengaturan privasi menyusul');
    }
    if (item.action === 'logout') {
        await auth.logout();
        await router.push('/login');
    }
}
</script>

<template>
    <AppShell>
        <section class="page active">
            <div class="page-intro">
                <p class="eyebrow">Akun</p>
                <h1>Pengaturan TanamYuk</h1>
            </div>
            <ProfileCard :name="name" :email="email" />
            <section class="section">
                <article class="plan-card">
                    <div class="plan-top">
                        <div>
                            <h2>TanamYuk Plus</h2>
                            <p>Aktif sampai 22 September 2027</p>
                        </div>
                        <span class="plan-pill">Aktif</span>
                    </div>
                    <div class="plan-footer">
                        <span>Paket tahunan • Rp249.000</span>
                        <button type="button" @click="router.push('/paket')">Kelola paket</button>
                    </div>
                </article>
            </section>
            <SettingsGroup title="Aplikasi" :items="appItems" @select="onSelect" />
            <SettingsGroup title="Bantuan dan akun" :items="helpItems" @select="onSelect" />
        </section>
    </AppShell>
</template>
