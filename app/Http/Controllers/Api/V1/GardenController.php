<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreGardenRequest;
use App\Http\Requests\Api\V1\UpdateGardenRequest;
use App\Models\CultivationMethod;
use App\Models\Garden;
use App\Services\EntitlementService;
use App\Support\PivotSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GardenController extends ApiController
{
    public function __construct(private readonly EntitlementService $entitlements) {}

    public function index(Request $request): JsonResponse
    {
        $gardens = Garden::query()
            ->where('user_id', $this->userId())
            ->with('cultivationMethods')
            ->withCount('plantingCycles')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->get();

        return $this->ok($gardens);
    }

    public function store(StoreGardenRequest $request): JsonResponse
    {
        $data = $request->validated();
        $status = $data['status'] ?? 'active';

        if ($status === 'active' && ! $this->entitlements->canCreateActiveGarden($request->user())) {
            return $this->fail('Paket Gratis hanya mengizinkan 1 kebun aktif.', 403);
        }

        $garden = Garden::query()->create([
            'user_id' => $this->userId(),
            'name' => $data['name'],
            'location_type' => $data['location_type'] ?? null,
            'area_m2' => $data['area_m2'] ?? null,
            'length_m' => $data['length_m'] ?? null,
            'width_m' => $data['width_m'] ?? null,
            'sunlight_hours' => $data['sunlight_hours'] ?? null,
            'shade_level' => $data['shade_level'] ?? null,
            'water_source' => $data['water_source'] ?? null,
            'drainage_level' => $data['drainage_level'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $status,
            'client_created_at' => now(),
        ]);

        $this->syncMethods($garden, $data);

        return $this->ok($garden->load('cultivationMethods')->loadCount('plantingCycles'), 'Kebun dibuat.', 201);
    }

    public function show(string $garden): JsonResponse
    {
        $model = $this->findGarden($garden);

        return $this->ok($model->load(['cultivationMethods', 'plantingCycles.commodity'])->loadCount('plantingCycles'));
    }

    public function update(UpdateGardenRequest $request, string $garden): JsonResponse
    {
        $model = $this->findGarden($garden);
        $data = $request->validated();
        $nextStatus = $data['status'] ?? $model->status;

        if ($nextStatus === 'active' && $model->status !== 'active' && ! $this->entitlements->canCreateActiveGarden($request->user(), $model->id)) {
            return $this->fail('Paket Gratis hanya mengizinkan 1 kebun aktif.', 403);
        }

        $model->fill(collect($data)->except(['cultivation_method_ids', 'cultivation_method_codes'])->all());
        $model->server_version = ((int) $model->server_version) + 1;
        $model->save();

        if ($request->exists('cultivation_method_ids') || $request->exists('cultivation_method_codes')) {
            $this->syncMethods($model, $data);
        }

        return $this->ok($model->load('cultivationMethods')->loadCount('plantingCycles'), 'Kebun diperbarui.');
    }

    public function destroy(string $garden): JsonResponse
    {
        $model = $this->findGarden($garden);
        $model->delete();

        return $this->ok(null, 'Kebun dihapus.');
    }

    private function findGarden(string $id): Garden
    {
        /** @var Garden $garden */
        $garden = $this->owned(Garden::class, $id);

        return $garden;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMethods(Garden $garden, array $data): void
    {
        $ids = $data['cultivation_method_ids'] ?? [];
        $codes = $data['cultivation_method_codes'] ?? [];

        if ($codes !== []) {
            $fromCodes = CultivationMethod::query()->whereIn('code', $codes)->pluck('id')->all();
            $ids = [...$ids, ...$fromCodes];
        }

        if ($ids === [] && $codes === [] && ! array_key_exists('cultivation_method_ids', $data) && ! array_key_exists('cultivation_method_codes', $data)) {
            return;
        }

        PivotSync::sync($garden->cultivationMethods(), $ids);
    }
}
