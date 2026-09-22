import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/tasks', { params }).then((response) => asList(unwrap(response)));
}

export function show(id) {
    return client.get(`/tasks/${id}`).then(unwrap);
}

export function create(payload) {
    return client.post('/tasks', payload).then(unwrap);
}

export function update(id, payload) {
    return client.patch(`/tasks/${id}`, payload).then(unwrap);
}

export function complete(id) {
    return client.post(`/tasks/${id}/complete`).then(unwrap);
}

export function reopen(id) {
    return client.post(`/tasks/${id}/reopen`).then(unwrap);
}
