<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const items = [
    { to: '/beranda', label: 'Beranda', icon: 'i-home', match: ['/beranda'] },
    { to: '/kebun', label: 'Kebun', icon: 'i-leaf', match: ['/kebun', '/siklus'] },
    { to: '/agenda', label: 'Agenda', icon: 'i-calendar', match: ['/agenda'] },
    { to: '/catatan', label: 'Catatan', icon: 'i-book', match: ['/catatan'] },
    { to: '/akun', label: 'Akun', icon: 'i-user', match: ['/akun', '/paket', '/pembayaran', '/bantuan'] },
];

function isActive(item) {
    return item.match.some((prefix) => route.path === prefix || route.path.startsWith(`${prefix}/`));
}
</script>

<template>
    <nav class="bottom-nav" aria-label="Navigasi utama">
        <div class="nav-inner">
            <RouterLink
                v-for="item in items"
                :key="item.to"
                :to="item.to"
                class="nav-item"
                :class="{ active: isActive(item) }"
            >
                <svg><use :href="`#${item.icon}`"></use></svg>
                <span>{{ item.label }}</span>
            </RouterLink>
        </div>
    </nav>
</template>
