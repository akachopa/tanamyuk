import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/expenses', { params }).then((response) => asList(unwrap(response)));
}

export function create(payload) {
    return client.post('/expenses', payload).then(unwrap);
}
