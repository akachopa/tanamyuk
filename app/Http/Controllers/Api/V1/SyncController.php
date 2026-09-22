<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SyncMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends ApiController
{
    public function push(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => ['nullable', 'uuid'],
            'mutations' => ['required', 'array', 'min:1'],
            'mutations.*.client_mutation_id' => ['required', 'string', 'max:255'],
            'mutations.*.entity_type' => ['required', 'string', 'max:100'],
            'mutations.*.entity_id' => ['nullable', 'uuid'],
            'mutations.*.operation' => ['required', 'string', 'in:create,update,delete'],
            'mutations.*.payload' => ['nullable', 'array'],
        ]);

        $results = [];

        foreach ($data['mutations'] as $mutation) {
            $existing = SyncMutation::query()
                ->where('user_id', $this->userId())
                ->where('client_mutation_id', $mutation['client_mutation_id'])
                ->first();

            if ($existing) {
                $results[] = [
                    'client_mutation_id' => $existing->client_mutation_id,
                    'processing_status' => $existing->processing_status,
                    'duplicate' => true,
                ];

                continue;
            }

            $payload = $mutation['payload'] ?? null;
            $row = SyncMutation::query()->create([
                'user_id' => $this->userId(),
                'device_id' => $data['device_id'] ?? null,
                'client_mutation_id' => $mutation['client_mutation_id'],
                'entity_type' => $mutation['entity_type'],
                'entity_id' => $mutation['entity_id'] ?? null,
                'operation' => $mutation['operation'],
                'payload_hash' => $payload ? hash('sha256', json_encode($payload) ?: '') : null,
                'processing_status' => 'processed',
                'processed_at' => now(),
            ]);

            $results[] = [
                'client_mutation_id' => $row->client_mutation_id,
                'processing_status' => $row->processing_status,
                'duplicate' => false,
            ];
        }

        return $this->ok([
            'mutations' => $results,
        ], 'Perubahan sinkron diproses.');
    }

    public function pull(Request $request): JsonResponse
    {
        $request->validate([
            'since' => ['nullable', 'date'],
            'device_id' => ['nullable', 'uuid'],
        ]);

        return $this->ok([
            'changes' => [],
            'deleted' => [],
            'server_time' => now()->toIso8601String(),
        ], 'Delta kosong.');
    }
}
