import client, { asList, unwrap } from './client';

export function list() {
    return client.get('/plans').then((response) => asList(unwrap(response)));
}

export function createOrder(payload) {
    return client.post('/orders', payload).then(unwrap);
}

export function showOrder(orderNumber) {
    return client.get(`/orders/${orderNumber}`).then(unwrap);
}
