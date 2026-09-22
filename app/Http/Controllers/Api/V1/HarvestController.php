<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Harvest;
use App\Models\PlantingCycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HarvestController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $harvests = Harvest::query()
            ->where('user_id', $this->userId())
            ->with('plantingCycle.commodity')
            ->when($request->filled('planting_cycle_id'), fn ($query) => $query->where('planting_cycle_id', $request->string('planting_cycle_id')))
            ->orderByDesc('harvest_date')
            ->get();

        return $this->ok($harvests);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'planting_cycle_id' => ['required_without:cycle_id', 'nullable', 'uuid'],
            'cycle_id' => ['required_without:planting_cycle_id', 'nullable', 'uuid'],
            'harvest_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'grade' => ['nullable', 'string', 'max:50'],
            'usage_type' => ['nullable', 'string', 'max:50'],
            'sale_value' => ['nullable', 'numeric', 'min:0'],
            'waste_quantity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'client_mutation_id' => ['nullable', 'string', 'max:255'],
        ]);

        $cycleId = $data['planting_cycle_id'] ?? $data['cycle_id'];
        unset($data['cycle_id']);
        $this->ownedCycle($cycleId);
        $data['planting_cycle_id'] = $cycleId;

        $harvest = Harvest::query()->create([
            ...$data,
            'user_id' => $this->userId(),
        ]);

        return $this->ok($harvest->load('plantingCycle.commodity'), 'Panen dicatat.', 201);
    }

    public function update(Request $request, string $harvest): JsonResponse
    {
        /** @var Harvest $model */
        $model = $this->owned(Harvest::class, $harvest);

        $data = $request->validate([
            'planting_cycle_id' => ['sometimes', 'uuid'],
            'harvest_date' => ['sometimes', 'date'],
            'quantity' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:50'],
            'grade' => ['nullable', 'string', 'max:50'],
            'usage_type' => ['nullable', 'string', 'max:50'],
            'sale_value' => ['nullable', 'numeric', 'min:0'],
            'waste_quantity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($data['planting_cycle_id'])) {
            $this->ownedCycle($data['planting_cycle_id']);
        }

        $model->fill($data);
        $model->server_version = ((int) $model->server_version) + 1;
        $model->save();

        return $this->ok($model->load('plantingCycle.commodity'), 'Panen diperbarui.');
    }

    private function ownedCycle(string $id): void
    {
        $exists = PlantingCycle::query()->where('user_id', $this->userId())->whereKey($id)->exists();

        if (! $exists) {
            throw ValidationException::withMessages(['planting_cycle_id' => 'Siklus tanam tidak ditemukan.']);
        }
    }
}
