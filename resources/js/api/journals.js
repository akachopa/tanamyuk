import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/journals', { params }).then((response) => asList(unwrap(response)));
}

export function create(payload) {
    return client.post('/journals', payload).then(unwrap);
}
