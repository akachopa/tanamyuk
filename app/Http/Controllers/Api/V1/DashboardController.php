<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Garden;
use App\Models\Harvest;
use App\Models\PlantingCycle;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function home(Request $request): JsonResponse
    {
        $userId = $this->userId();

        $tasksToday = Task::query()
            ->with(['plantingCycle.commodity'])
            ->where('user_id', $userId)
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        $overdue = Task::query()
            ->with(['plantingCycle.commodity'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'postponed'])
            ->where('due_at', '<', now()->startOfDay())
            ->orderBy('due_at')
            ->get();

        $cycles = PlantingCycle::query()
            ->with(['commodity', 'currentStage', 'garden'])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('start_date')
            ->get()
            ->map(function (PlantingCycle $cycle) {
                $dayNumber = $cycle->start_date
                    ? ((int) $cycle->start_date->copy()->startOfDay()->diffInDays(now()->startOfDay())) + 1
                    : null;

                return [
                    'id' => $cycle->id,
                    'name' => $cycle->name,
                    'status' => $cycle->status,
                    'day_number' => $dayNumber,
                    'stage_name' => $cycle->currentStage?->name,
                    'quantity' => $cycle->quantity,
                    'quantity_unit' => $cycle->quantity_unit,
                    'target_harvest_date' => $cycle->target_harvest_date,
                    'commodity' => $cycle->commodity?->only(['id', 'name', 'slug']),
                    'garden' => $cycle->garden?->only(['id', 'name']),
                ];
            });

        $harvestMonth = Harvest::query()
            ->where('user_id', $userId)
            ->whereBetween('harvest_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->selectRaw('unit, SUM(quantity) as total')
            ->groupBy('unit')
            ->get()
            ->map(fn ($row) => [
                'unit' => $row->unit,
                'total' => (float) $row->total,
            ])
            ->values();

        $gardens = Garden::query()
            ->where('user_id', $userId)
            ->withCount(['plantingCycles as active_cycles_count' => fn ($query) => $query->where('status', 'active')])
            ->orderBy('name')
            ->get();

        return $this->ok([
            'tasks_today' => $tasksToday->map(fn (Task $task) => $this->presentTask($task))->values(),
            'overdue_tasks' => $overdue->map(fn (Task $task) => $this->presentTask($task, true))->values(),
            'active_cycles' => $cycles,
            'harvest_month' => $harvestMonth,
            'harvest_month_total_kg' => (float) $harvestMonth->where('unit', 'kg')->sum('total'),
            'gardens' => $gardens,
            'counts' => [
                'tasks_today' => $tasksToday->count(),
                'overdue' => $overdue->count(),
                'active_cycles' => $cycles->count(),
                'gardens' => $gardens->count(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentTask(Task $task, bool $late = false): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'instruction' => $task->instruction,
            'task_type' => $task->task_type,
            'status' => $task->status,
            'scheduled_at' => $task->scheduled_at,
            'due_at' => $task->due_at,
            'completed_at' => $task->completed_at,
            'done' => $task->status === 'completed',
            'late' => $late || ($task->status !== 'completed' && $task->due_at && $task->due_at->lt(now()->startOfDay())),
            'planting_cycle' => $task->plantingCycle ? [
                'id' => $task->plantingCycle->id,
                'name' => $task->plantingCycle->name,
                'commodity' => $task->plantingCycle->commodity?->only(['id', 'name', 'slug']),
            ] : null,
        ];
    }
}
