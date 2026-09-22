<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Garden;
use App\Models\JournalEntry;
use App\Models\PlantingCycle;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JournalController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $entries = JournalEntry::query()
            ->where('user_id', $this->userId())
            ->with(['garden', 'plantingCycle.commodity', 'task'])
            ->when($request->filled('garden_id'), fn ($query) => $query->where('garden_id', $request->string('garden_id')))
            ->when($request->filled('planting_cycle_id'), fn ($query) => $query->where('planting_cycle_id', $request->string('planting_cycle_id')))
            ->orderByDesc('entry_date')
            ->get();

        return $this->ok($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'garden_id' => ['required', 'uuid'],
            'planting_cycle_id' => ['nullable', 'uuid'],
            'cycle_id' => ['nullable', 'uuid'],
            'task_id' => ['nullable', 'uuid'],
            'activity_type' => ['required', 'string', 'max:50'],
            'entry_date' => ['required', 'date'],
            'plant_condition' => ['nullable', 'string', 'max:50'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'client_mutation_id' => ['nullable', 'string', 'max:255'],
        ]);

        $data['planting_cycle_id'] = $data['planting_cycle_id'] ?? ($data['cycle_id'] ?? null);
        unset($data['cycle_id']);
        $this->assertLinks($data);

        $entry = JournalEntry::query()->create([
            ...$data,
            'user_id' => $this->userId(),
        ]);

        return $this->ok($entry->load(['garden', 'plantingCycle.commodity']), 'Jurnal disimpan.', 201);
    }

    public function update(Request $request, string $journal): JsonResponse
    {
        /** @var JournalEntry $entry */
        $entry = $this->owned(JournalEntry::class, $journal);

        $data = $request->validate([
            'garden_id' => ['sometimes', 'uuid'],
            'planting_cycle_id' => ['nullable', 'uuid'],
            'task_id' => ['nullable', 'uuid'],
            'activity_type' => ['sometimes', 'string', 'max:50'],
            'entry_date' => ['sometimes', 'date'],
            'plant_condition' => ['nullable', 'string', 'max:50'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->assertLinks([
            'garden_id' => $data['garden_id'] ?? $entry->garden_id,
            'planting_cycle_id' => $data['planting_cycle_id'] ?? $entry->planting_cycle_id,
            'task_id' => $data['task_id'] ?? $entry->task_id,
        ]);

        $entry->fill($data);
        $entry->server_version = ((int) $entry->server_version) + 1;
        $entry->save();

        return $this->ok($entry->load(['garden', 'plantingCycle.commodity']), 'Jurnal diperbarui.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertLinks(array $data): void
    {
        if (! empty($data['garden_id']) && ! Garden::query()->where('user_id', $this->userId())->whereKey($data['garden_id'])->exists()) {
            throw ValidationException::withMessages(['garden_id' => 'Kebun tidak ditemukan.']);
        }

        if (! empty($data['planting_cycle_id'])) {
            $cycle = PlantingCycle::query()->where('user_id', $this->userId())->find($data['planting_cycle_id']);
            if (! $cycle) {
                throw ValidationException::withMessages(['planting_cycle_id' => 'Siklus tanam tidak ditemukan.']);
            }
            if (! empty($data['garden_id']) && $cycle->garden_id !== $data['garden_id']) {
                throw ValidationException::withMessages(['planting_cycle_id' => 'Siklus tidak berada di kebun ini.']);
            }
        }

        if (! empty($data['task_id']) && ! Task::query()->where('user_id', $this->userId())->whereKey($data['task_id'])->exists()) {
            throw ValidationException::withMessages(['task_id' => 'Tugas tidak ditemukan.']);
        }
    }
}
