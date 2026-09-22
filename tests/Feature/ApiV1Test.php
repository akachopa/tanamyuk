<?php

namespace Tests\Feature;

use App\Models\PlantingCycle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_and_demo_dashboard(): void
    {
        $this->seed();

        $this->assertCount(15, $this->getJson('/api/v1/commodities')->assertOk()->json('data'));
        $this->assertCount(3, $this->getJson('/api/v1/plans')->assertOk()->json('data'));
        $this->assertGreaterThanOrEqual(8, count($this->getJson('/api/v1/articles/faqs')->assertOk()->json('data')));

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@tanamyuk.com',
            'password' => 'password',
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.user.name', 'Robbi');

        $token = $login->json('data.token');

        $dashboard = $this->withToken($token)->getJson('/api/v1/dashboard')->assertOk();
        $dashboard->assertJsonPath('data.gardens.0.name', 'Kebun Samping Rumah');
        $dashboard->assertJsonPath('data.counts.active_cycles', 3);
        $dashboard->assertJsonPath('data.counts.tasks_today', 3);
        $dashboard->assertJsonPath('data.counts.overdue', 1);
        $this->assertEqualsWithDelta(4.8, $dashboard->json('data.harvest_month_total_kg'), 0.01);

        $this->assertSame(
            'Pembesaran',
            PlantingCycle::query()->where('name', 'Pakcoy Hidroponik')->firstOrFail()->currentStage->name
        );
        $this->assertSame(
            'Pembungaan',
            PlantingCycle::query()->where('name', 'Cabai Rawit')->firstOrFail()->currentStage->name
        );
        $this->assertSame(
            'Vegetatif',
            PlantingCycle::query()->where('name', 'Tomat')->firstOrFail()->currentStage->name
        );
    }

    public function test_register_enforces_free_plan_and_recommendations(): void
    {
        $this->seed();

        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'Sari',
            'email' => 'Sari@Example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonPath('success', true)->assertJsonPath('data.user.email', 'sari@example.com');

        $this->assertDatabaseHas('product_events', [
            'event_name' => 'registration_completed',
        ]);

        $token = $register->json('data.token');

        $garden = $this->withToken($token)->postJson('/api/v1/gardens', [
            'name' => 'Balkon',
            'area_m2' => 2,
            'sunlight_hours' => 5,
            'cultivation_method_codes' => ['pot'],
        ])->assertCreated();

        $this->withToken($token)->postJson('/api/v1/gardens', [
            'name' => 'Kedua',
        ])->assertForbidden()->assertJsonPath('success', false);

        $pakcoy = $this->getJson('/api/v1/commodities/pakcoy')->assertOk()->json('data');

        $cycle = $this->withToken($token)->postJson('/api/v1/cycles', [
            'garden_id' => $garden->json('data.id'),
            'commodity_id' => $pakcoy['id'],
            'template_id' => $pakcoy['templates'][0]['id'],
            'name' => 'Pakcoy 1',
            'start_date' => now()->toDateString(),
            'quantity' => 4,
            'quantity_unit' => 'pot',
        ])->assertCreated();

        $activated = $this->withToken($token)
            ->postJson('/api/v1/cycles/'.$cycle->json('data.id').'/activate')
            ->assertOk();

        $this->assertNotEmpty($activated->json('data.tasks'));

        $assessment = $this->postJson('/api/v1/assessments', [
            'answers' => [
                'area_m2' => 4,
                'sunlight_hours' => 4,
                'water_source' => 'keran',
                'care_minutes' => 20,
                'experience' => 'baru',
                'goal' => 'konsumsi',
                'method' => 'polybag',
            ],
        ])->assertCreated();

        $top = $assessment->json('data.recommendations.0.commodity.slug');
        $this->assertContains($top, ['bayam', 'kangkung', 'pakcoy', 'sawi', 'selada']);
        $this->assertIsArray($assessment->json('data.recommendations.0.reasons'));

        $planId = collect($this->getJson('/api/v1/plans')->json('data'))->firstWhere('code', 'plus_monthly')['id'];
        $order = $this->withToken($token)->postJson('/api/v1/orders', [
            'plan_id' => $planId,
        ])->assertCreated();

        $this->withToken($token)
            ->getJson('/api/v1/orders/'.$order->json('data.order_number').'/status')
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');

        $this->withToken($token)->postJson('/api/v1/sync/push', [
            'mutations' => [[
                'client_mutation_id' => 'm-1',
                'entity_type' => 'journal',
                'operation' => 'create',
                'payload' => ['notes' => 'offline'],
            ]],
        ])->assertOk()->assertJsonPath('data.mutations.0.processing_status', 'processed');

        $this->withToken($token)->getJson('/api/v1/sync/pull')
            ->assertOk()
            ->assertJsonPath('data.changes', []);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/dashboard')->assertUnauthorized()->assertJsonPath('success', false);
    }
}
