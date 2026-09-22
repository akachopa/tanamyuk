<?php

namespace App\Services;

use App\Models\CycleStage;
use App\Models\PlantingCycle;
use App\Models\Task;
use App\Models\TemplateTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgendaGeneratorService
{
    public function activate(PlantingCycle $cycle): PlantingCycle
    {
        return DB::transaction(function () use ($cycle) {
            $cycle->loadMissing(['template', 'commodity']);

            if ($cycle->start_date === null) {
                $cycle->start_date = now()->toDateString();
            }

            $start = Carbon::parse($cycle->start_date)->startOfDay();
            $template = $cycle->template;

            if ($template && ! $cycle->stages()->exists()) {
                $this->copyTemplate($cycle, $start);
            }

            if ($cycle->stages()->exists()) {
                $cycle->current_stage_id = $this->syncStageProgress($cycle);
            }

            if (! $cycle->target_harvest_date) {
                $days = $template?->duration_days ?? $cycle->commodity?->harvest_min_days;
                if ($days) {
                    $cycle->target_harvest_date = $start->copy()->addDays(max(0, ((int) $days) - 1))->toDateString();
                }
            }

            $cycle->status = 'active';
            $cycle->template_version = $template?->version ?? $cycle->template_version;
            $cycle->server_version = ((int) $cycle->server_version) + 1;
            $cycle->save();

            return $cycle->fresh([
                'garden',
                'commodity',
                'variety',
                'cultivationMethod',
                'template',
                'currentStage',
                'stages',
                'tasks',
            ]);
        });
    }

    private function copyTemplate(PlantingCycle $cycle, Carbon $start): void
    {
        $template = $cycle->template;
        $stageMap = [];

        foreach ($template->stages()->orderBy('sort_order')->get() as $stage) {
            $created = CycleStage::query()->create([
                'planting_cycle_id' => $cycle->id,
                'source_template_stage_id' => $stage->id,
                'name' => $stage->name,
                'description' => $stage->description,
                'planned_start_date' => $start->copy()->addDays((int) $stage->start_day_offset)->toDateString(),
                'planned_end_date' => $stage->end_day_offset === null
                    ? null
                    : $start->copy()->addDays((int) $stage->end_day_offset)->toDateString(),
                'sort_order' => $stage->sort_order,
            ]);

            $stageMap[$stage->id] = $created->id;
        }

        foreach ($template->tasks()->orderBy('sort_order')->get() as $task) {
            foreach ($this->offsets($task) as $offset) {
                $scheduled = $start->copy()->addDays($offset)->setTime(7, 0);

                Task::query()->create([
                    'user_id' => $cycle->user_id,
                    'planting_cycle_id' => $cycle->id,
                    'cycle_stage_id' => $stageMap[$task->stage_id] ?? null,
                    'source_template_task_id' => $task->id,
                    'task_type' => $task->task_type,
                    'title' => $task->title,
                    'instruction' => $task->instruction,
                    'scheduled_at' => $scheduled,
                    'due_at' => $scheduled->copy()->setTime(18, 0),
                    'status' => 'pending',
                    'is_manual' => false,
                ]);
            }
        }
    }

    private function syncStageProgress(PlantingCycle $cycle): ?string
    {
        $today = now()->startOfDay();
        $currentId = null;

        $stages = $cycle->stages()->orderBy('sort_order')->get();

        foreach ($stages as $stage) {
            $stageStart = $stage->planned_start_date
                ? Carbon::parse($stage->planned_start_date)->startOfDay()
                : null;
            $stageEnd = $stage->planned_end_date
                ? Carbon::parse($stage->planned_end_date)->startOfDay()
                : null;

            if (! $stageStart || $stageStart->gt($today)) {
                continue;
            }

            $stage->started_at = $stage->started_at ?? $stageStart;

            if ($stageEnd && $stageEnd->lt($today)) {
                $stage->completed_at = $stage->completed_at ?? $stageEnd->copy()->setTime(18, 0);
                $stage->save();

                continue;
            }

            $stage->completed_at = null;
            $stage->save();
            $currentId = $stage->id;
        }

        return $currentId ?? $stages->first()?->id;
    }

    /**
     * @return array<int, int>
     */
    private function offsets(TemplateTask $task): array
    {
        $first = (int) $task->first_day_offset;
        $interval = (int) ($task->repeat_interval_days ?? 0);
        $until = $task->repeat_until_day_offset;

        if ($interval > 0 && $until !== null && (int) $until >= $first) {
            $offsets = [];

            for ($day = $first; $day <= (int) $until; $day += $interval) {
                $offsets[] = $day;

                if (count($offsets) >= 200) {
                    break;
                }
            }

            return $offsets;
        }

        return [$first];
    }
}
