import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/articles', { params }).then((response) => asList(unwrap(response)));
}
