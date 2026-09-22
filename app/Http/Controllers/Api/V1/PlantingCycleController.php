<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StorePlantingCycleRequest;
use App\Http\Requests\Api\V1\UpdatePlantingCycleRequest;
use App\Models\CommodityVariety;
use App\Models\CultivationTemplate;
use App\Models\Garden;
use App\Models\PlantingCycle;
use App\Services\AgendaGeneratorService;
use App\Services\EntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlantingCycleController extends ApiController
{
    public function __construct(
        private readonly AgendaGeneratorService $agenda,
        private readonly EntitlementService $entitlements,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cycles = PlantingCycle::query()
            ->where('user_id', $this->userId())
            ->with(['commodity', 'garden', 'currentStage', 'cultivationMethod', 'template'])
            ->withCount(['tasks', 'harvests'])
            ->when($request->filled('garden_id'), fn ($query) => $query->where('garden_id', $request->string('garden_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('start_date')
            ->get()
            ->each(fn (PlantingCycle $cycle) => $this->decorate($cycle));

        return $this->ok($cycles);
    }

    public function store(StorePlantingCycleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $shouldActivate = ($data['status'] ?? 'draft') === 'active';
        unset($data['status']);
        $this->assertRelations($data);

        if ($shouldActivate && ! $this->entitlements->canActivateCycle($request->user())) {
            return $this->fail('Paket Gratis hanya mengizinkan 3 siklus tanam aktif.', 403);
        }

        if ($shouldActivate && empty($data['template_id'])) {
            $template = CultivationTemplate::query()
                ->where('commodity_id', $data['commodity_id'])
                ->where('status', 'published')
                ->latest('published_at')
                ->first();

            if ($template) {
                $data['template_id'] = $template->id;
            }
        }

        $cycle = PlantingCycle::query()->create([
            ...$data,
            'user_id' => $this->userId(),
            'status' => 'draft',
        ]);

        if ($shouldActivate) {
            $cycle = $this->agenda->activate($cycle);
        }

        return $this->ok($this->decorate($cycle->load($this->relations())), 'Siklus tanam dibuat.', 201);
    }

    public function show(string $plantingCycle): JsonResponse
    {
        $cycle = $this->findCycle($plantingCycle)->load([
            ...$this->relations(),
            'stages',
            'tasks',
        ]);

        return $this->ok($this->decorate($cycle));
    }

    public function update(UpdatePlantingCycleRequest $request, string $plantingCycle): JsonResponse
    {
        $cycle = $this->findCycle($plantingCycle);
        $data = $request->validated();
        $this->assertRelations($data, $cycle);

        $cycle->fill($data);
        $cycle->server_version = ((int) $cycle->server_version) + 1;
        $cycle->save();

        return $this->ok($this->decorate($cycle->load($this->relations())), 'Siklus tanam diperbarui.');
    }

    public function activate(Request $request, string $plantingCycle): JsonResponse
    {
        $cycle = $this->findCycle($plantingCycle);

        if ($cycle->status === 'completed') {
            return $this->fail('Siklus yang sudah selesai tidak bisa diaktifkan.', 422);
        }

        if ($cycle->status !== 'active' && ! $this->entitlements->canActivateCycle($request->user())) {
            return $this->fail('Paket Gratis hanya mengizinkan 3 siklus tanam aktif.', 403);
        }

        $cycle = $this->agenda->activate($cycle);

        return $this->ok($this->decorate($cycle), 'Siklus tanam diaktifkan.');
    }

    public function complete(string $plantingCycle): JsonResponse
    {
        $cycle = $this->findCycle($plantingCycle);

        if ($cycle->status !== 'completed') {
            $cycle->status = 'completed';
            $cycle->server_version = ((int) $cycle->server_version) + 1;
            $cycle->save();
            $cycle->stages()->whereNull('completed_at')->update(['completed_at' => now()]);
        }

        return $this->ok($this->decorate($cycle->load($this->relations())), 'Siklus tanam diselesaikan.');
    }

    private function findCycle(string $id): PlantingCycle
    {
        /** @var PlantingCycle $cycle */
        $cycle = $this->owned(PlantingCycle::class, $id);

        return $cycle;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertRelations(array $data, ?PlantingCycle $current = null): void
    {
        $gardenId = $data['garden_id'] ?? $current?->garden_id;
        $commodityId = $data['commodity_id'] ?? $current?->commodity_id;

        if ($gardenId) {
            $ownsGarden = Garden::query()->where('user_id', $this->userId())->whereKey($gardenId)->exists();
            if (! $ownsGarden) {
                throw ValidationException::withMessages(['garden_id' => 'Kebun tidak ditemukan.']);
            }
        }

        if (! empty($data['variety_id'])) {
            $variety = CommodityVariety::query()->find($data['variety_id']);
            if (! $variety || ($commodityId && $variety->commodity_id !== $commodityId)) {
                throw ValidationException::withMessages(['variety_id' => 'Varietas tidak sesuai komoditas.']);
            }
        }

        if (! empty($data['template_id'])) {
            $template = CultivationTemplate::query()->find($data['template_id']);
            if (! $template || ($commodityId && $template->commodity_id !== $commodityId)) {
                throw ValidationException::withMessages(['template_id' => 'Template tidak sesuai komoditas.']);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return ['commodity', 'garden', 'currentStage', 'cultivationMethod', 'template', 'variety'];
    }

    private function decorate(PlantingCycle $cycle): PlantingCycle
    {
        $dayNumber = null;

        if ($cycle->start_date) {
            $elapsed = $cycle->start_date->copy()->startOfDay()->diffInDays(now()->startOfDay());
            $dayNumber = (int) $elapsed + 1;
        }

        $duration = $cycle->template?->duration_days ?? $cycle->commodity?->harvest_max_days;
        $progress = ($dayNumber && $duration) ? (int) min(100, round(($dayNumber / $duration) * 100)) : null;

        $cycle->setAttribute('day_number', $dayNumber);
        $cycle->setAttribute('stage_name', $cycle->currentStage?->name);
        $cycle->setAttribute('progress_percent', $progress);

        return $cycle;
    }
}
