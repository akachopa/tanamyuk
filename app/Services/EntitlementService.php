<?php

namespace App\Services;

use App\Models\Garden;
use App\Models\PlanFeature;
use App\Models\PlantingCycle;
use App\Models\Subscription;
use App\Models\User;

class EntitlementService
{
    public function activeSubscription(User $user): ?Subscription
    {
        return Subscription::query()
            ->with(['plan.planFeatures.feature'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->orderByDesc('starts_at')
            ->first();
    }

    /**
     * Null means the limit is unlimited.
     */
    public function limit(User $user, string $featureCode, int $freeFallback): ?int
    {
        $subscription = $this->activeSubscription($user);

        if (! $subscription || ! $subscription->plan) {
            return $freeFallback;
        }

        /** @var PlanFeature|null $planFeature */
        $planFeature = $subscription->plan->planFeatures->first(
            fn (PlanFeature $feature) => $feature->feature?->code === $featureCode
        );

        if (! $planFeature || $planFeature->value_number === null) {
            return $subscription->plan->is_free ? $freeFallback : null;
        }

        return (int) $planFeature->value_number;
    }

    public function canCreateActiveGarden(User $user, ?string $ignoreGardenId = null): bool
    {
        $limit = $this->limit($user, 'max_active_gardens', 1);

        if ($limit === null) {
            return true;
        }

        $count = Garden::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->when($ignoreGardenId, fn ($query) => $query->whereKeyNot($ignoreGardenId))
            ->count();

        return $count < $limit;
    }

    public function canActivateCycle(User $user): bool
    {
        $limit = $this->limit($user, 'max_active_cycles', 3);

        if ($limit === null) {
            return true;
        }

        $count = PlantingCycle::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        return $count < $limit;
    }

    /**
     * @return array{max_active_gardens: int|null, max_active_cycles: int|null, active_gardens: int, active_cycles: int}
     */
    public function snapshot(User $user): array
    {
        return [
            'max_active_gardens' => $this->limit($user, 'max_active_gardens', 1),
            'max_active_cycles' => $this->limit($user, 'max_active_cycles', 3),
            'active_gardens' => Garden::query()->where('user_id', $user->id)->where('status', 'active')->count(),
            'active_cycles' => PlantingCycle::query()->where('user_id', $user->id)->where('status', 'active')->count(),
        ];
    }
}
