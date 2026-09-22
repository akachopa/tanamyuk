import client, { unwrap } from './client';

export function login(payload) {
    return client.post('/auth/login', payload).then(unwrap);
}

export function register(payload) {
    return client.post('/auth/register', payload).then(unwrap);
}

export function logout() {
    return client.post('/auth/logout').then(unwrap);
}

export function me() {
    return client.get('/auth/me').then(unwrap);
}

export function updateMe(payload) {
    return client.patch('/auth/me', payload).then(unwrap);
}

export function forgotPassword(payload) {
    return client.post('/auth/forgot-password', payload).then(unwrap);
}
