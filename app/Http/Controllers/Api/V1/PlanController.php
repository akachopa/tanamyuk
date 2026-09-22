<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Plan;
use App\Services\EntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends ApiController
{
    public function __construct(private readonly EntitlementService $entitlements) {}

    public function index(): JsonResponse
    {
        $plans = Plan::query()
            ->with('planFeatures.feature')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Plan $plan) => $this->present($plan));

        return $this->ok($plans);
    }

    public function subscription(Request $request): JsonResponse
    {
        $subscription = $this->entitlements->activeSubscription($request->user());

        return $this->ok([
            'subscription' => $subscription,
            'plan' => $subscription?->plan ? $this->present($subscription->plan) : null,
            'limits' => $this->entitlements->snapshot($request->user()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Plan $plan): array
    {
        $highlights = [];
        $entitlements = [];

        foreach ($plan->planFeatures as $planFeature) {
            $feature = $planFeature->feature;
            if (! $feature) {
                continue;
            }

            if ($feature->code === 'marketing_highlights') {
                $decoded = json_decode((string) $planFeature->value_text, true);
                $highlights = is_array($decoded) ? $decoded : [];

                continue;
            }

            $entitlements[] = [
                'code' => $feature->code,
                'name' => $feature->name,
                'value_type' => $feature->value_type,
                'value' => match ($feature->value_type) {
                    'boolean' => $planFeature->value_boolean,
                    'number' => $planFeature->value_number === null ? null : (float) $planFeature->value_number,
                    default => $planFeature->value_text,
                },
            ];
        }

        $period = match ($plan->code) {
            'free' => 'Selamanya',
            'plus_monthly' => 'per bulan',
            'plus_yearly' => 'per tahun',
            default => $plan->billing_period,
        };

        return [
            'id' => $plan->id,
            'code' => $plan->code,
            'name' => $plan->name,
            'description' => $plan->description,
            'billing_period' => $plan->billing_period,
            'duration_days' => $plan->duration_days,
            'price' => (float) $plan->price,
            'price_label' => 'Rp'.number_format((float) $plan->price, 0, ',', '.'),
            'period' => $period,
            'currency' => $plan->currency,
            'is_free' => $plan->is_free,
            'is_featured' => $plan->is_featured,
            'featured' => $plan->is_featured,
            'features' => $highlights,
            'entitlements' => $entitlements,
        ];
    }
}
