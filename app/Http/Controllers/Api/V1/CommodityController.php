<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Commodity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommodityController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $commodities = Commodity::query()
            ->with(['category', 'cultivationMethods'])
            ->where('status', 'active')
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($category) => $category->where('slug', $request->string('category')));
            })
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->get();

        return $this->ok($commodities);
    }

    public function show(string $slug): JsonResponse
    {
        $commodity = Commodity::query()
            ->with(['category', 'cultivationMethods', 'varieties'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $commodity->setRelation(
            'templates',
            $commodity->templates()->where('status', 'published')->with(['stages', 'cultivationMethod'])->get()
        );

        return $this->ok($commodity);
    }
}
