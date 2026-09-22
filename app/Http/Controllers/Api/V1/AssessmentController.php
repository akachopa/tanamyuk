<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AssessmentSubmission;
use App\Models\RecommendationResult;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends ApiController
{
    public function __construct(private readonly RecommendationService $recommendations) {}

    public function questions(): JsonResponse
    {
        return $this->ok([
            'steps' => [
                [
                    'key' => 'area_m2',
                    'title' => 'Seberapa luas lahanmu?',
                    'options' => [
                        ['label' => 'Kurang dari 2 m²', 'value' => 1],
                        ['label' => '2–5 m²', 'value' => 4],
                        ['label' => '6–15 m²', 'value' => 10],
                        ['label' => 'Lebih dari 15 m²', 'value' => 20],
                    ],
                ],
                [
                    'key' => 'sunlight_hours',
                    'title' => 'Berapa jam sinar matahari?',
                    'options' => [
                        ['label' => 'Kurang dari 3 jam', 'value' => 2],
                        ['label' => '3–5 jam', 'value' => 4],
                        ['label' => '6–8 jam', 'value' => 7],
                        ['label' => 'Lebih dari 8 jam', 'value' => 9],
                    ],
                ],
                [
                    'key' => 'water_source',
                    'title' => 'Sumber air utama?',
                    'options' => [
                        ['label' => 'Keran', 'value' => 'keran'],
                        ['label' => 'Ember atau tandon', 'value' => 'ember'],
                        ['label' => 'Air hujan', 'value' => 'hujan'],
                        ['label' => 'Sumur', 'value' => 'sumur'],
                    ],
                ],
                [
                    'key' => 'care_minutes',
                    'title' => 'Waktu perawatan per hari?',
                    'options' => [
                        ['label' => 'Di bawah 15 menit', 'value' => 10],
                        ['label' => '15–30 menit', 'value' => 20],
                        ['label' => '30–60 menit', 'value' => 45],
                        ['label' => 'Lebih dari 1 jam', 'value' => 75],
                    ],
                ],
                [
                    'key' => 'experience',
                    'title' => 'Pengalaman menanam?',
                    'options' => [
                        ['label' => 'Baru mulai', 'value' => 'baru'],
                        ['label' => 'Pernah menanam', 'value' => 'pernah'],
                        ['label' => 'Sudah rutin', 'value' => 'rutin'],
                    ],
                ],
                [
                    'key' => 'goal',
                    'title' => 'Tujuan utama berkebun?',
                    'options' => [
                        ['label' => 'Konsumsi keluarga', 'value' => 'konsumsi'],
                        ['label' => 'Hobi', 'value' => 'hobi'],
                        ['label' => 'Hemat belanja', 'value' => 'hemat'],
                        ['label' => 'Jualan kecil', 'value' => 'jualan'],
                    ],
                ],
                [
                    'key' => 'method',
                    'title' => 'Metode yang kamu suka?',
                    'options' => [
                        ['label' => 'Polybag', 'value' => 'polybag'],
                        ['label' => 'Tanam tanah', 'value' => 'tanah'],
                        ['label' => 'Hidroponik', 'value' => 'hidroponik'],
                        ['label' => 'Vertikultur', 'value' => 'vertikultur'],
                    ],
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.area_m2' => ['nullable', 'numeric', 'min:0'],
            'answers.space_m2' => ['nullable', 'numeric', 'min:0'],
            'answers.sunlight_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'answers.care_minutes' => ['nullable', 'numeric', 'min:0', 'max:1440'],
            'answers.daily_minutes' => ['nullable', 'numeric', 'min:0', 'max:1440'],
            'answers.experience' => ['nullable', 'string', 'max:50'],
            'answers.water' => ['nullable', 'string', 'max:50'],
            'answers.water_source' => ['nullable', 'string', 'max:50'],
            'answers.goal' => ['nullable', 'string', 'max:50'],
            'answers.preference' => ['nullable', 'string', 'max:50'],
            'answers.method' => ['nullable', 'string', 'max:50'],
            'anonymous_session_id' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:15'],
        ]);

        $limit = (int) ($data['limit'] ?? $request->integer('limit', 5));
        $user = auth('sanctum')->user();
        $recommendations = $this->recommendations->recommend($data['answers'], $limit);

        $submission = DB::transaction(function () use ($data, $user, $recommendations) {
            $submission = AssessmentSubmission::query()->create([
                'user_id' => $user?->id,
                'anonymous_session_id' => $data['anonymous_session_id'] ?? null,
                'answers' => $data['answers'],
                'completed_at' => now(),
            ]);

            foreach ($recommendations as $row) {
                RecommendationResult::query()->create([
                    'assessment_submission_id' => $submission->id,
                    'commodity_id' => $row['commodity_id'],
                    'score' => $row['score'],
                    'reasons' => $row['reasons'],
                    'rank' => $row['rank'],
                ]);
            }

            return $submission;
        });

        return $this->ok([
            'id' => $submission->id,
            'answers' => $submission->answers,
            'completed_at' => $submission->completed_at,
            'recommendations' => $recommendations,
        ], 'Rekomendasi tanaman siap.', 201);
    }

    public function recommendations(string $assessment): JsonResponse
    {
        $submission = AssessmentSubmission::query()
            ->with(['results.commodity.category'])
            ->findOrFail($assessment);

        $viewer = auth('sanctum')->user();

        if ($submission->user_id && $viewer && $viewer->id !== $submission->user_id) {
            return $this->fail('Anda tidak memiliki akses ke asesmen ini.', 403);
        }

        $recommendations = $submission->results
            ->sortBy('rank')
            ->values()
            ->map(fn (RecommendationResult $result) => [
                'rank' => $result->rank,
                'score' => $result->score,
                'reasons' => $result->reasons ?? [],
                'commodity_id' => $result->commodity_id,
                'commodity' => [
                    'id' => $result->commodity?->id,
                    'name' => $result->commodity?->name,
                    'slug' => $result->commodity?->slug,
                    'category' => $result->commodity?->category?->name,
                ],
            ]);

        return $this->ok([
            'id' => $submission->id,
            'recommendations' => $recommendations,
        ]);
    }
}
