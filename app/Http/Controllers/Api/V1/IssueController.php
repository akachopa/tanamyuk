<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PlantingCycle;
use App\Models\PlantIssue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IssueController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $issues = PlantIssue::query()
            ->where('user_id', $this->userId())
            ->with('plantingCycle.commodity')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('planting_cycle_id'), fn ($query) => $query->where('planting_cycle_id', $request->string('planting_cycle_id')))
            ->orderByDesc('discovered_at')
            ->get();

        return $this->ok($issues);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'planting_cycle_id' => ['required_without:cycle_id', 'nullable', 'uuid'],
            'cycle_id' => ['required_without:planting_cycle_id', 'nullable', 'uuid'],
            'discovered_at' => ['nullable', 'date'],
            'symptom_category' => ['nullable', 'string', 'max:50'],
            'affected_part' => ['nullable', 'string', 'max:50'],
            'severity' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'action_taken' => ['nullable', 'string'],
            'status' => ['nullable', 'in:open,monitoring,resolved'],
            'client_mutation_id' => ['nullable', 'string', 'max:255'],
        ]);

        $cycleId = $data['planting_cycle_id'] ?? $data['cycle_id'];
        $this->ownedCycle($cycleId);

        $issue = PlantIssue::query()->create([
            'user_id' => $this->userId(),
            'planting_cycle_id' => $cycleId,
            'discovered_at' => $data['discovered_at'] ?? now(),
            'symptom_category' => $data['symptom_category'] ?? null,
            'affected_part' => $data['affected_part'] ?? null,
            'severity' => $data['severity'] ?? null,
            'description' => $data['description'] ?? null,
            'action_taken' => $data['action_taken'] ?? null,
            'status' => $data['status'] ?? 'open',
            'resolved_at' => ($data['status'] ?? null) === 'resolved' ? now() : null,
            'client_mutation_id' => $data['client_mutation_id'] ?? null,
        ]);

        return $this->ok($issue->load('plantingCycle.commodity'), 'Masalah tanaman dicatat.', 201);
    }

    public function update(Request $request, string $issue): JsonResponse
    {
        /** @var PlantIssue $model */
        $model = $this->owned(PlantIssue::class, $issue);

        $data = $request->validate([
            'discovered_at' => ['nullable', 'date'],
            'symptom_category' => ['nullable', 'string', 'max:50'],
            'affected_part' => ['nullable', 'string', 'max:50'],
            'severity' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'action_taken' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:open,monitoring,resolved'],
        ]);

        if (($data['status'] ?? null) === 'resolved' && ! $model->resolved_at) {
            $data['resolved_at'] = now();
        }

        $model->fill($data);
        $model->server_version = ((int) $model->server_version) + 1;
        $model->save();

        return $this->ok($model->load('plantingCycle.commodity'), 'Masalah tanaman diperbarui.');
    }

    private function ownedCycle(string $id): PlantingCycle
    {
        $cycle = PlantingCycle::query()->where('user_id', $this->userId())->find($id);

        if (! $cycle) {
            throw ValidationException::withMessages(['planting_cycle_id' => 'Siklus tanam tidak ditemukan.']);
        }

        return $cycle;
    }
}
