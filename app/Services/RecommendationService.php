<?php

namespace App\Services;

use App\Models\Commodity;
use App\Models\RecommendationRule;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * @param  array<string, mixed>  $answers
     * @return array<int, array<string, mixed>>
     */
    public function recommend(array $answers, int $limit = 5): array
    {
        $context = $this->context($answers);
        $rules = RecommendationRule::query()
            ->where('status', 'active')
            ->orderByDesc('priority')
            ->get();

        $commodities = Commodity::query()
            ->with(['category', 'cultivationMethods', 'templates'])
            ->where('status', 'active')
            ->get();

        $scored = $commodities->map(function (Commodity $commodity) use ($context, $rules) {
            return $this->scoreCommodity($commodity, $context, $rules);
        })->sortBy([
            ['score', 'desc'],
            ['name', 'asc'],
        ])->values();

        return $scored->take(max(1, $limit))->values()->map(function (array $row, int $index) {
            $row['rank'] = $index + 1;

            return $row;
        })->all();
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     */
    private function context(array $answers): array
    {
        $experience = $this->normalizeExperience((string) ($answers['experience'] ?? ''));

        return [
            'area_m2' => $answers['area_m2'] ?? $answers['space_m2'] ?? null,
            'space_m2' => $answers['space_m2'] ?? $answers['area_m2'] ?? null,
            'sunlight_hours' => $answers['sunlight_hours'] ?? null,
            'care_minutes' => $answers['care_minutes'] ?? $answers['daily_minutes'] ?? null,
            'daily_minutes' => $answers['daily_minutes'] ?? $answers['care_minutes'] ?? null,
            'experience' => $answers['experience'] ?? null,
            'experience_level' => $experience,
            'water_source' => $answers['water_source'] ?? null,
            'water' => $this->waterLevel($answers),
            'goal' => $answers['goal'] ?? null,
            'method' => $answers['method'] ?? null,
            'preference' => $answers['preference'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  Collection<int, RecommendationRule>  $rules
     * @return array<string, mixed>
     */
    private function scoreCommodity(Commodity $commodity, array $context, Collection $rules): array
    {
        $requirements = $this->requirements($commodity);
        $reasons = [];

        [$light, $lightReason] = $this->lightScore($commodity, $requirements, $context);
        [$space, $spaceReason] = $this->spaceScore($commodity, $requirements, $context);
        [$care, $careReason] = $this->careScore($commodity, $requirements, $context);
        [$experience, $experienceReason] = $this->experienceScore($commodity, $requirements, $context);
        [$water, $waterReason] = $this->waterScore($commodity, $requirements, $context);
        [$goal, $goalReason] = $this->goalScore($commodity, $context);
        [$preference, $preferenceReason] = $this->preferenceScore($commodity, $context);

        foreach ([
            $lightReason,
            $spaceReason,
            $careReason,
            $experienceReason,
            $waterReason,
            $goalReason,
            $preferenceReason,
        ] as $reason) {
            if ($reason) {
                $reasons[] = $reason;
            }
        }

        $score = $light + $space + $care + $experience + $water + $goal + $preference;
        $ruleDelta = 0;

        foreach ($rules as $rule) {
            if ($rule->commodity_id && $rule->commodity_id !== $commodity->id) {
                continue;
            }

            if ($rule->category_id && $rule->category_id !== $commodity->category_id) {
                continue;
            }

            if (! $this->ruleMatches($rule, $context)) {
                continue;
            }

            $ruleDelta += (int) $rule->score_delta;

            if ($rule->reason_template) {
                $reasons[] = str_replace('{commodity}', $commodity->name, $rule->reason_template);
            }
        }

        $method = $this->preferredMethod($commodity, $context);

        return [
            'commodity_id' => $commodity->id,
            'name' => $commodity->name,
            'score' => (int) max(0, min(100, round($score + $ruleDelta))),
            'reasons' => array_values(array_unique($reasons)),
            'method' => $method?->name ?? '',
            'cultivation_method' => $method?->name ?? '',
            'commodity' => [
                'id' => $commodity->id,
                'name' => $commodity->name,
                'slug' => $commodity->slug,
                'category' => $commodity->category?->name,
                'difficulty_level' => $commodity->difficulty_level,
                'harvest_min_days' => $commodity->harvest_min_days,
                'harvest_max_days' => $commodity->harvest_max_days,
            ],
            'factors' => [
                'light' => $light,
                'space' => $space,
                'care_time' => $care,
                'experience' => $experience,
                'water' => $water,
                'goal' => $goal,
                'preference' => $preference,
                'rules' => $ruleDelta,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function requirements(Commodity $commodity): array
    {
        $template = $commodity->templates->firstWhere('status', 'published');

        return is_array($template?->requirements) ? $template->requirements : [];
    }

    /**
     * @param  array<string, mixed>  $requirements
     * @param  array<string, mixed>  $context
     * @return array{0: int, 1: ?string}
     */
    private function lightScore(Commodity $commodity, array $requirements, array $context): array
    {
        $hours = $context['sunlight_hours'];
        $min = $commodity->sunlight_min_hours ?? ($requirements['sunlight_min_hours'] ?? null);
        $max = $commodity->sunlight_max_hours ?? ($requirements['sunlight_max_hours'] ?? null);

        if ($hours === null || ($min === null && $max === null)) {
            return [15, null];
        }

        $hours = (float) $hours;
        $lo = $min === null ? 0 : (float) $min;
        $hi = $max === null ? 24 : (float) $max;

        if ($hours >= $lo && $hours <= $hi) {
            return [25, 'Jam sinar matahari sesuai.'];
        }

        $distance = $hours < $lo ? $lo - $hours : $hours - $hi;
        $points = (int) max(0, round(25 - ($distance * 8)));

        return [$points, $points >= 15 ? 'Cahaya masih cukup dekat dengan kebutuhan tanaman.' : null];
    }

    /**
     * @param  array<string, mixed>  $requirements
     * @param  array<string, mixed>  $context
     * @return array{0: int, 1: ?string}
     */
    private function spaceScore(Commodity $commodity, array $requirements, array $context): array
    {
        $space = $context['area_m2'];
        $min = $commodity->min_space_m2 ?? ($requirements['min_space_m2'] ?? null);

        if ($space === null || $min === null) {
            return [12, null];
        }

        $space = (float) $space;
        $min = (float) $min;

        if ($min <= 0 || $space >= $min) {
            return [20, 'Lahan cukup untuk '.$commodity->name.'.'];
        }

        $points = (int) round(20 * ($space / $min));

        return [$points, $points >= 10 ? 'Lahan agak sempit, tetap bisa dicoba dengan pot.' : null];
    }

    /**
     * @param  array<string, mixed>  $requirements
     * @param  array<string, mixed>  $context
     * @return array{0: int, 1: ?string}
     */
    private function careScore(Commodity $commodity, array $requirements, array $context): array
    {
        $have = $context['care_minutes'];
        $need = $commodity->maintenance_minutes_per_day ?? ($requirements['maintenance_minutes_per_day'] ?? null);

        if ($have === null || $need === null) {
            return [10, null];
        }

        $have = (float) $have;
        $need = (float) $need;

        if ($need <= 0 || $have >= $need) {
            return [20, 'Waktu perawatan harian mencukupi.'];
        }

        $points = (int) round(20 * ($have / $need));

        return [$points, null];
    }

    /**
     * @param  array<string, mixed>  $requirements
     * @param  array<string, mixed>  $context
     * @return array{0: int, 1: ?string}
     */
    private function experienceScore(Commodity $commodity, array $requirements, array $context): array
    {
        $level = $context['experience_level'] ?: 'menengah';
        $difficulty = $this->normalizeDifficulty((string) ($commodity->difficulty_level ?? ($requirements['difficulty_level'] ?? 'sedang')));

        $matrix = [
            'pemula' => ['mudah' => 10, 'sedang' => 5, 'sulit' => 1],
            'menengah' => ['mudah' => 8, 'sedang' => 10, 'sulit' => 6],
            'mahir' => ['mudah' => 8, 'sedang' => 10, 'sulit' => 10],
        ];

        $points = $matrix[$level][$difficulty] ?? 5;
        $reason = $level === 'pemula' && $difficulty === 'mudah' ? 'Ramah untuk pemula.' : null;

        return [$points, $reason];
    }

    /**
     * @param  array<string, mixed>  $requirements
     * @param  array<string, mixed>  $context
     * @return array{0: int, 1: ?string}
     */
    private function waterScore(Commodity $commodity, array $requirements, array $context): array
    {
        $levels = ['rendah' => 1, 'sedang' => 2, 'tinggi' => 3];
        $user = $levels[$context['water']] ?? 2;
        $plantLevel = $this->normalizeWater((string) ($commodity->water_need_level ?? ($requirements['water_need_level'] ?? 'sedang')));
        $plant = $levels[$plantLevel] ?? 2;
        $gap = abs($user - $plant);

        $points = match ($gap) {
            0 => 10,
            1 => 5,
            default => 1,
        };

        $reason = $gap === 0 ? 'Kebutuhan air sesuai kondisi lahan.' : null;

        return [$points, $reason];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{0: int, 1: ?string}
     */
    private function goalScore(Commodity $commodity, array $context): array
    {
        $goal = (string) ($context['goal'] ?? '');
        $slug = $commodity->category?->slug;
        $days = (int) ($commodity->harvest_max_days ?? 90);

        $points = match ($goal) {
            'konsumsi', 'hemat' => $days <= 45 ? 10 : ($days <= 90 ? 6 : 3),
            'jualan' => $slug === 'buah' ? 10 : 4,
            'hobi' => in_array($slug, ['bumbu', 'sayuran-daun'], true) ? 10 : 6,
            default => 5,
        };

        $reason = $points >= 8 ? 'Sesuai tujuan berkebunmu.' : null;

        return [$points, $reason];
    }

    /**
     * Preference is 5 points. A chosen cultivation method fills this factor
     * when the user did not pick a crop category.
     *
     * @param  array<string, mixed>  $context
     * @return array{0: int, 1: ?string}
     */
    private function preferenceScore(Commodity $commodity, array $context): array
    {
        $preference = (string) ($context['preference'] ?? '');

        if ($preference !== '') {
            if (in_array($preference, ['apa_saja', 'any'], true)) {
                return [5, null];
            }

            $map = [
                'daun' => 'sayuran-daun',
                'buah' => 'buah',
                'bumbu' => 'bumbu',
            ];

            if (($map[$preference] ?? null) === $commodity->category?->slug) {
                return [5, 'Sesuai jenis tanaman yang kamu suka.'];
            }

            return [0, null];
        }

        $method = $this->preferredMethod($commodity, $context);
        $requested = $this->methodCode($context['method'] ?? null);

        if (! $requested) {
            return [3, null];
        }

        if ($method && $method->code === $requested) {
            $suitability = (int) ($method->pivot->suitability_score ?? 80);

            return [
                (int) max(1, round(5 * ($suitability / 100))),
                'Cocok dengan metode '.$method->name.'.',
            ];
        }

        if ($requested === 'pot' && $commodity->cultivationMethods->contains('code', 'pot')) {
            return [4, 'Bisa ditanam di wadah vertikal atau polybag.'];
        }

        return [1, null];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function preferredMethod(Commodity $commodity, array $context): ?object
    {
        $requested = $this->methodCode($context['method'] ?? null);

        if ($requested) {
            $match = $commodity->cultivationMethods->firstWhere('code', $requested);
            if ($match) {
                return $match;
            }
        }

        return $commodity->cultivationMethods
            ->sortByDesc(fn ($method) => (int) ($method->pivot->suitability_score ?? 0))
            ->first();
    }

    private function methodCode(mixed $method): ?string
    {
        return match ((string) $method) {
            'polybag', 'pot', 'vertikultur' => 'pot',
            'tanah' => 'tanah',
            'hidroponik' => 'hidroponik',
            '', '0' => null,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function ruleMatches(RecommendationRule $rule, array $context): bool
    {
        $actual = $context[$rule->input_field] ?? null;
        $expected = $rule->comparison_value;

        if ($actual === null) {
            return false;
        }

        return match ($rule->operator) {
            'eq', '=' => (string) $actual === (string) $expected,
            'neq', '!=' => (string) $actual !== (string) $expected,
            'gt', '>' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
            'gte', '>=' => is_numeric($actual) && is_numeric($expected) && (float) $actual >= (float) $expected,
            'lt', '<' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
            'lte', '<=' => is_numeric($actual) && is_numeric($expected) && (float) $actual <= (float) $expected,
            'contains' => str_contains(mb_strtolower((string) $actual), mb_strtolower((string) $expected)),
            'in' => in_array((string) $actual, array_map('trim', explode(',', (string) $expected)), true),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $answers
     */
    private function waterLevel(array $answers): string
    {
        if (! empty($answers['water'])) {
            return $this->normalizeWater((string) $answers['water']);
        }

        return match ((string) ($answers['water_source'] ?? '')) {
            'keran' => 'tinggi',
            'sumur', 'ember' => 'sedang',
            'hujan' => 'rendah',
            default => 'sedang',
        };
    }

    private function normalizeExperience(string $value): string
    {
        return match (mb_strtolower($value)) {
            'baru', 'pemula', 'beginner' => 'pemula',
            'pernah', 'menengah', 'intermediate' => 'menengah',
            'rutin', 'mahir', 'advanced' => 'mahir',
            default => 'menengah',
        };
    }

    private function normalizeDifficulty(string $value): string
    {
        return match (mb_strtolower($value)) {
            'mudah', 'easy' => 'mudah',
            'sulit', 'hard' => 'sulit',
            default => 'sedang',
        };
    }

    private function normalizeWater(string $value): string
    {
        return match (mb_strtolower($value)) {
            'rendah', 'low' => 'rendah',
            'tinggi', 'high' => 'tinggi',
            default => 'sedang',
        };
    }
}
