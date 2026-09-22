import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/gardens', { params }).then((response) => asList(unwrap(response)));
}

export function show(id) {
    return client.get(`/gardens/${id}`).then(unwrap);
}

export function create(payload) {
    return client.post('/gardens', payload).then(unwrap);
}

export function update(id, payload) {
    return client.put(`/gardens/${id}`, payload).then(unwrap);
}

export function remove(id) {
    return client.delete(`/gardens/${id}`).then(unwrap);
}
