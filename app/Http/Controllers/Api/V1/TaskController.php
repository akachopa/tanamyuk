<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CompleteTaskRequest;
use App\Models\CycleStage;
use App\Models\PlantingCycle;
use App\Models\Task;
use App\Models\TaskEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->where('user_id', $this->userId())
            ->with(['plantingCycle.commodity', 'cycleStage'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('planting_cycle_id'), fn ($query) => $query->where('planting_cycle_id', $request->string('planting_cycle_id')))
            ->when($request->filled('cycle_id'), fn ($query) => $query->where('planting_cycle_id', $request->string('cycle_id')))
            ->when($request->string('scope')->toString() === 'today', function ($query) {
                $query->where(function ($inner) {
                    $inner->whereDate('scheduled_at', today())
                        ->orWhere(function ($overdue) {
                            $overdue->whereIn('status', ['pending', 'postponed'])
                                ->where('due_at', '<', now()->startOfDay());
                        });
                });
            })
            ->when($request->string('scope')->toString() === 'overdue', function ($query) {
                $query->whereIn('status', ['pending', 'postponed'])
                    ->where('due_at', '<', now()->startOfDay());
            })
            ->when($request->string('scope')->toString() === 'upcoming', function ($query) {
                $query->whereIn('status', ['pending', 'postponed'])
                    ->where('scheduled_at', '>', now()->endOfDay());
            })
            ->when($request->filled('from'), fn ($query) => $query->where('scheduled_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->where('scheduled_at', '<=', $request->date('to')))
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn (Task $task) => $this->present($task));

        return $this->ok($tasks);
    }

    public function show(string $task): JsonResponse
    {
        return $this->ok($this->present($this->findTask($task)->load(['plantingCycle.commodity', 'cycleStage', 'events'])));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'task_type' => ['required', 'string', 'max:50'],
            'instruction' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'planting_cycle_id' => ['nullable', 'uuid'],
            'cycle_id' => ['nullable', 'uuid'],
            'cycle_stage_id' => ['nullable', 'uuid'],
        ]);

        $cycleId = $data['planting_cycle_id'] ?? $data['cycle_id'] ?? null;
        $cycle = $cycleId ? $this->ownedCycle($cycleId) : null;

        if (! empty($data['cycle_stage_id'])) {
            $stage = CycleStage::query()->find($data['cycle_stage_id']);
            if (! $stage || ($cycle && $stage->planting_cycle_id !== $cycle->id)) {
                throw ValidationException::withMessages(['cycle_stage_id' => 'Tahap siklus tidak ditemukan.']);
            }
        }

        $task = Task::query()->create([
            'user_id' => $this->userId(),
            'planting_cycle_id' => $cycle?->id,
            'cycle_stage_id' => $data['cycle_stage_id'] ?? null,
            'task_type' => $data['task_type'],
            'title' => $data['title'],
            'instruction' => $data['instruction'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'due_at' => $data['due_at'] ?? ($data['scheduled_at'] ?? null),
            'status' => 'pending',
            'is_manual' => true,
        ]);

        $this->recordEvent($task, 'created');

        return $this->ok($this->present($task->load('plantingCycle.commodity')), 'Tugas dibuat.', 201);
    }

    public function update(Request $request, string $task): JsonResponse
    {
        $model = $this->findTask($task);
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'task_type' => ['sometimes', 'string', 'max:50'],
            'instruction' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:pending,completed,postponed,skipped'],
        ]);

        if (($data['status'] ?? null) === 'completed' && empty($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        $model->fill($data);
        $model->server_version = ((int) $model->server_version) + 1;
        $model->save();
        $this->recordEvent($model, 'updated');

        return $this->ok($this->present($model->load('plantingCycle.commodity')), 'Tugas diperbarui.');
    }

    public function complete(CompleteTaskRequest $request, string $task): JsonResponse
    {
        $model = $this->findTask($task);
        $data = $request->validated();

        $model->status = 'completed';
        $model->completed_at = $data['completed_at'] ?? now();
        $model->server_version = ((int) $model->server_version) + 1;
        $model->save();

        $this->recordEvent($model, 'completed', $data['notes'] ?? null);

        return $this->ok($this->present($model->load('plantingCycle.commodity')), 'Tugas selesai.');
    }

    public function postpone(Request $request, string $task): JsonResponse
    {
        $model = $this->findTask($task);
        $data = $request->validate([
            'postponed_to' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $model->status = 'postponed';
        $model->postponed_to = $data['postponed_to'];
        $model->scheduled_at = $data['postponed_to'];
        $model->due_at = $data['postponed_to'];
        $model->server_version = ((int) $model->server_version) + 1;
        $model->save();

        $this->recordEvent($model, 'postponed', $data['notes'] ?? null);

        return $this->ok($this->present($model->load('plantingCycle.commodity')), 'Tugas ditunda.');
    }

    public function reopen(string $task): JsonResponse
    {
        $model = $this->findTask($task);
        $model->status = 'pending';
        $model->completed_at = null;
        $model->server_version = ((int) $model->server_version) + 1;
        $model->save();

        $this->recordEvent($model, 'reopened');

        return $this->ok($this->present($model->load('plantingCycle.commodity')), 'Tugas dibuka kembali.');
    }

    private function findTask(string $id): Task
    {
        /** @var Task $task */
        $task = $this->owned(Task::class, $id);

        return $task;
    }

    private function ownedCycle(string $id): PlantingCycle
    {
        $cycle = PlantingCycle::query()->where('user_id', $this->userId())->find($id);

        if (! $cycle) {
            throw ValidationException::withMessages(['planting_cycle_id' => 'Siklus tanam tidak ditemukan.']);
        }

        return $cycle;
    }

    private function recordEvent(Task $task, string $type, ?string $notes = null): void
    {
        TaskEvent::query()->create([
            'task_id' => $task->id,
            'user_id' => $this->userId(),
            'event_type' => $type,
            'event_at_server' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Task $task): array
    {
        $late = in_array($task->status, ['pending', 'postponed'], true)
            && $task->due_at
            && $task->due_at->lt(now()->startOfDay());

        return [
            'id' => $task->id,
            'title' => $task->title,
            'instruction' => $task->instruction,
            'task_type' => $task->task_type,
            'status' => $task->status,
            'scheduled_at' => $task->scheduled_at,
            'due_at' => $task->due_at,
            'completed_at' => $task->completed_at,
            'postponed_to' => $task->postponed_to,
            'is_manual' => $task->is_manual,
            'done' => $task->status === 'completed',
            'late' => $late,
            'planting_cycle_id' => $task->planting_cycle_id,
            'cycle_stage_id' => $task->cycle_stage_id,
            'planting_cycle' => $task->relationLoaded('plantingCycle') && $task->plantingCycle ? [
                'id' => $task->plantingCycle->id,
                'name' => $task->plantingCycle->name,
                'commodity' => $task->plantingCycle->commodity?->only(['id', 'name', 'slug']),
            ] : null,
        ];
    }
}
