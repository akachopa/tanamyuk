import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/issues', { params }).then((response) => asList(unwrap(response)));
}

export function create(payload) {
    return client.post('/issues', payload).then(unwrap);
}
