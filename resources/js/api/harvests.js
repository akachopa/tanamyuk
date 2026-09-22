import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/harvests', { params }).then((response) => asList(unwrap(response)));
}

export function create(payload) {
    return client.post('/harvests', payload).then(unwrap);
}
