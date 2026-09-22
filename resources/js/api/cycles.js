import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/cycles', { params }).then((response) => asList(unwrap(response)));
}

export function show(id) {
    return client.get(`/cycles/${id}`).then(unwrap);
}

export function create(payload) {
    return client.post('/cycles', payload).then(unwrap);
}

export function update(id, payload) {
    return client.put(`/cycles/${id}`, payload).then(unwrap);
}
