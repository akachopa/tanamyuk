import client, { asList, unwrap } from './client';

export function list(params) {
    return client.get('/commodities', { params }).then((response) => asList(unwrap(response)));
}
