import { defineStore } from 'pinia';
import { ref } from 'vue';
import * as authApi from '@/api/auth';
import * as gardensApi from '@/api/gardens';

const TOKEN_KEY = 'tanamyuk_token';
const USER_KEY = 'tanamyuk_user';
const ONBOARDING_KEY = 'tanamyuk_onboarding_done';

function readUser() {
    try {
        return JSON.parse(localStorage.getItem(USER_KEY) || 'null');
    } catch {
        return null;
    }
}

function pickToken(payload) {
    return payload?.token || payload?.access_token || payload?.plainTextToken || payload?.plain_text_token || '';
}

function pickUser(payload) {
    if (!payload || typeof payload !== 'object') {
        return null;
    }
    if (payload.user && typeof payload.user === 'object') {
        return payload.user;
    }
    if (payload.email || payload.name) {
        return payload;
    }
    return null;
}

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem(TOKEN_KEY) || '');
    const user = ref(readUser());
    const gardensCount = ref(null);
    let bootPromise = null;
    let onboardingPromise = null;

    function setToken(value) {
        token.value = value || '';
        if (value) {
            localStorage.setItem(TOKEN_KEY, value);
        } else {
            localStorage.removeItem(TOKEN_KEY);
        }
    }

    function setUser(value) {
        user.value = value;
        if (value) {
            localStorage.setItem(USER_KEY, JSON.stringify(value));
        } else {
            localStorage.removeItem(USER_KEY);
        }
    }

    function applySession(payload) {
        const nextToken = pickToken(payload);
        const nextUser = pickUser(payload);
        if (nextToken) {
            setToken(nextToken);
        }
        if (nextUser) {
            setUser(nextUser);
        }
        return nextUser;
    }

    async function login(payload) {
        const data = await authApi.login(payload);
        const nextUser = applySession(data);
        if (!token.value) {
            throw new Error('Token tidak ditemukan pada respons masuk.');
        }
        onboardingPromise = null;
        return nextUser;
    }

    async function register(payload) {
        const data = await authApi.register(payload);
        const nextUser = applySession(data);
        if (!token.value) {
            throw new Error('Token tidak ditemukan pada respons daftar.');
        }
        onboardingPromise = null;
        return nextUser;
    }

    async function fetchMe() {
        if (!token.value) {
            return null;
        }
        try {
            const data = await authApi.me();
            const nextUser = pickUser(data) || data;
            setUser(nextUser);
            return user.value;
        } catch (error) {
            if (error?.response?.status === 401) {
                setToken('');
                setUser(null);
            }
            throw error;
        }
    }

    function bootstrap() {
        if (!bootPromise) {
            bootPromise = (async () => {
                if (token.value) {
                    try {
                        await fetchMe();
                    } catch {
                        // Keep the cached profile when the network is unavailable.
                    }
                }
            })();
        }
        return bootPromise;
    }

    async function logout() {
        try {
            if (token.value) {
                await authApi.logout();
            }
        } catch {
            // Clear the local session even if the server is unreachable.
        }
        setToken('');
        setUser(null);
        gardensCount.value = null;
        bootPromise = null;
        onboardingPromise = null;
        localStorage.removeItem(ONBOARDING_KEY);
    }

    async function updateProfile(payload) {
        try {
            const data = await authApi.updateMe(payload);
            const nextUser = pickUser(data) || { ...(user.value || {}), ...payload };
            setUser(nextUser);
        } catch {
            setUser({ ...(user.value || {}), ...payload });
        }
        return user.value;
    }

    function markOnboardingDone() {
        localStorage.setItem(ONBOARDING_KEY, '1');
        onboardingPromise = Promise.resolve(false);
    }

    function needsOnboarding() {
        if (!token.value) {
            return Promise.resolve(false);
        }
        if (localStorage.getItem(ONBOARDING_KEY) === '1' || user.value?.primary_goal) {
            onboardingPromise = Promise.resolve(false);
            return onboardingPromise;
        }
        if (!onboardingPromise) {
            onboardingPromise = (async () => {
                try {
                    const gardens = await gardensApi.list();
                    gardensCount.value = gardens.length;
                    return gardens.length === 0;
                } catch {
                    return false;
                }
            })();
        }
        return onboardingPromise;
    }

    return {
        token,
        user,
        gardensCount,
        setUser,
        login,
        register,
        fetchMe,
        bootstrap,
        logout,
        updateProfile,
        markOnboardingDone,
        needsOnboarding,
    };
});
