import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'landing', component: () => import('@/pages/LandingPage.vue') },
        { path: '/asesmen', name: 'assessment', component: () => import('@/pages/AssessmentPage.vue') },
        { path: '/login', name: 'login', component: () => import('@/pages/LoginPage.vue'), meta: { guestOnly: true } },
        { path: '/register', name: 'register', component: () => import('@/pages/RegisterPage.vue'), meta: { guestOnly: true } },
        { path: '/lupa-kata-sandi', name: 'forgot', component: () => import('@/pages/ForgotPasswordPage.vue'), meta: { guestOnly: true } },
        { path: '/onboarding', name: 'onboarding', component: () => import('@/pages/OnboardingPage.vue'), meta: { requiresAuth: true, skipOnboarding: true } },
        { path: '/app', redirect: '/beranda' },
        { path: '/beranda', name: 'home', component: () => import('@/pages/HomePage.vue'), meta: { requiresAuth: true } },
        { path: '/kebun', name: 'garden', component: () => import('@/pages/GardenPage.vue'), meta: { requiresAuth: true } },
        { path: '/kebun/baru', name: 'garden-create', component: () => import('@/pages/GardenFormPage.vue'), meta: { requiresAuth: true } },
        { path: '/kebun/:id', name: 'garden-edit', component: () => import('@/pages/GardenFormPage.vue'), meta: { requiresAuth: true } },
        { path: '/agenda', name: 'agenda', component: () => import('@/pages/AgendaPage.vue'), meta: { requiresAuth: true } },
        { path: '/catatan', name: 'records', component: () => import('@/pages/RecordsPage.vue'), meta: { requiresAuth: true } },
        { path: '/catatan/jurnal/baru', name: 'journal-create', component: () => import('@/pages/JournalFormPage.vue'), meta: { requiresAuth: true } },
        { path: '/catatan/panen/baru', name: 'harvest-create', component: () => import('@/pages/HarvestFormPage.vue'), meta: { requiresAuth: true } },
        { path: '/catatan/biaya/baru', name: 'expense-create', component: () => import('@/pages/ExpenseFormPage.vue'), meta: { requiresAuth: true } },
        { path: '/catatan/masalah/baru', name: 'issue-create', component: () => import('@/pages/IssueFormPage.vue'), meta: { requiresAuth: true } },
        { path: '/akun', name: 'account', component: () => import('@/pages/AccountPage.vue'), meta: { requiresAuth: true } },
        { path: '/siklus/baru', name: 'cycle-create', component: () => import('@/pages/CycleFormPage.vue'), meta: { requiresAuth: true } },
        { path: '/paket', name: 'plans', component: () => import('@/pages/PlansPage.vue'), meta: { requiresAuth: true } },
        { path: '/pembayaran/:orderNumber', name: 'payment', component: () => import('@/pages/PaymentPage.vue'), meta: { requiresAuth: true } },
        { path: '/bantuan', name: 'help', component: () => import('@/pages/HelpPage.vue'), meta: { requiresAuth: true } },
        { path: '/:pathMatch(.*)*', redirect: '/' },
    ],
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    await auth.bootstrap();

    if (to.meta.requiresAuth && !auth.token) {
        return { path: '/login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && auth.token) {
        return { path: '/beranda' };
    }

    if (to.meta.requiresAuth && !to.meta.skipOnboarding && auth.token) {
        const required = await auth.needsOnboarding();
        if (required) {
            return { path: '/onboarding' };
        }
    }

    return true;
});

export default router;
