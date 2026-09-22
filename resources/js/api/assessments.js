import client, { unwrap } from './client';

export function submit(payload) {
    return client.post('/assessments', payload).then(unwrap);
}
