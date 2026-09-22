import axios from 'axios';

const client = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

client.interceptors.request.use((config) => {
    const token = localStorage.getItem('tanamyuk_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export function unwrap(response) {
    const body = response?.data ?? response;
    if (body && typeof body === 'object' && !Array.isArray(body) && Object.prototype.hasOwnProperty.call(body, 'data')) {
        return body.data;
    }
    return body;
}

export function asList(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }
    if (payload && Array.isArray(payload.data)) {
        return payload.data;
    }
    return [];
}

export function errorMessage(error, fallback = 'Terjadi kesalahan. Coba lagi.') {
    const data = error?.response?.data;
    if (data?.errors && typeof data.errors === 'object') {
        const first = Object.values(data.errors).flat()[0];
        if (first) {
            return first;
        }
    }
    return data?.message || error?.message || fallback;
}

export default client;
