<?php

namespace Database\Seeders;

use App\Models\Commodity;
use App\Models\CultivationMethod;
use App\Models\CultivationTemplate;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Garden;
use App\Models\Harvest;
use App\Models\JournalEntry;
use App\Models\Plan;
use App\Models\PlantingCycle;
use App\Models\PlantIssue;
use App\Models\Task;
use App\Models\User;
use App\Services\AgendaGeneratorService;
use App\Support\PivotSync;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->create([
            'name' => 'Robbi',
            'email' => 'demo@tanamyuk.com',
            'password' => 'password',
            'timezone' => 'Asia/Jakarta',
            'experience_level' => 'pernah',
            'daily_available_minutes' => 30,
            'primary_goal' => 'konsumsi',
            'status' => 'active',
        ]);

        $user->preferences()->create([
            'language' => 'id',
            'theme' => 'system',
            'reminder_time' => '07:00',
            'email_notifications' => true,
            'push_notifications' => true,
        ]);

        $freePlan = Plan::query()->where('code', 'free')->first();
        if ($freePlan) {
            $user->subscriptions()->create([
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now()->subMonths(2),
            ]);
        }

        $garden = Garden::query()->create([
            'user_id' => $user->id,
            'name' => 'Kebun Samping Rumah',
            'location_type' => 'pekarangan',
            'area_m2' => 12,
            'length_m' => 4,
            'width_m' => 3,
            'sunlight_hours' => 6,
            'shade_level' => 'sebagian',
            'water_source' => 'keran',
            'drainage_level' => 'baik',
            'notes' => 'Tanah Patah, Bengkulu. Lahan samping rumah, cahaya pagi sampai siang.',
            'status' => 'active',
            'client_created_at' => now()->subMonths(2),
        ]);

        $methodIds = CultivationMethod::query()->whereIn('code', ['tanah', 'pot', 'hidroponik'])->pluck('id');
        foreach ($methodIds as $methodId) {
            PivotSync::attach($garden->cultivationMethods(), $methodId);
        }

        $agenda = app(AgendaGeneratorService::class);
        $pakcoy = $this->activate($agenda, $user, $garden, 'pakcoy', 'hidroponik', 'Pakcoy Hidroponik', 21, 40, 'lubang');
        $cabai = $this->activate($agenda, $user, $garden, 'cabai-rawit', 'pot', 'Cabai Rawit', 47, 15, 'polybag');
        $tomat = $this->activate($agenda, $user, $garden, 'tomat', 'pot', 'Tomat', 34, 8, 'polybag');

        $kangkungCommodity = Commodity::query()->where('slug', 'kangkung')->firstOrFail();
        $kangkung = PlantingCycle::query()->create([
            'user_id' => $user->id,
            'garden_id' => $garden->id,
            'commodity_id' => $kangkungCommodity->id,
            'cultivation_method_id' => CultivationMethod::query()->where('code', 'pot')->value('id'),
            'name' => 'Kangkung',
            'start_date' => now()->subDays(40)->toDateString(),
            'target_harvest_date' => $this->inThisMonth(10)->toDateString(),
            'quantity' => 8,
            'quantity_unit' => 'polybag',
            'status' => 'completed',
            'notes' => 'Siklus sebelumnya, sudah dipanen untuk keluarga.',
        ]);

        Task::query()
            ->where('user_id', $user->id)
            ->where('scheduled_at', '<', now()->startOfDay())
            ->update([
                'status' => 'completed',
                'completed_at' => now()->subDay(),
            ]);

        Task::query()
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', today())
            ->update([
                'scheduled_at' => now()->addDay()->setTime(7, 0),
                'due_at' => now()->addDay()->setTime(18, 0),
            ]);

        Task::query()->create([
            'user_id' => $user->id,
            'task_type' => 'cek',
            'title' => 'Cek kondisi tanaman',
            'instruction' => 'Semua tanaman • selesai 07.12',
            'scheduled_at' => now()->setTime(7, 0),
            'due_at' => now()->setTime(18, 0),
            'status' => 'completed',
            'completed_at' => now()->setTime(7, 12),
            'is_manual' => true,
        ]);

        Task::query()->create([
            'user_id' => $user->id,
            'planting_cycle_id' => $cabai->id,
            'task_type' => 'siram',
            'title' => 'Siram cabai rawit',
            'instruction' => '15 tanaman • ±10 menit',
            'scheduled_at' => now()->setTime(17, 0),
            'due_at' => now()->setTime(18, 0),
            'status' => 'pending',
            'is_manual' => true,
        ]);

        Task::query()->create([
            'user_id' => $user->id,
            'planting_cycle_id' => $pakcoy->id,
            'task_type' => 'pupuk',
            'title' => 'Cek nutrisi pakcoy',
            'instruction' => '40 lubang • ±5 menit',
            'scheduled_at' => now()->setTime(17, 15),
            'due_at' => now()->setTime(18, 0),
            'status' => 'pending',
            'is_manual' => true,
        ]);

        Task::query()->create([
            'user_id' => $user->id,
            'planting_cycle_id' => $tomat->id,
            'task_type' => 'rawat',
            'title' => 'Pangkas daun tomat',
            'instruction' => '8 tanaman • ±15 menit',
            'scheduled_at' => now()->subDay()->setTime(17, 0),
            'due_at' => now()->subDay()->setTime(18, 0),
            'status' => 'pending',
            'is_manual' => true,
        ]);

        JournalEntry::query()->create([
            'user_id' => $user->id,
            'garden_id' => $garden->id,
            'planting_cycle_id' => $pakcoy->id,
            'activity_type' => 'dokumentasi',
            'entry_date' => now()->toDateString(),
            'plant_condition' => 'baik',
            'duration_minutes' => 5,
            'notes' => 'Foto perkembangan pakcoy. Daun segar, media lembab.',
        ]);

        Harvest::query()->create([
            'user_id' => $user->id,
            'planting_cycle_id' => $kangkung->id,
            'harvest_date' => $this->inThisMonth(2)->toDateString(),
            'quantity' => 1.8,
            'unit' => 'kg',
            'grade' => 'A',
            'usage_type' => 'konsumsi',
            'notes' => 'Panen kangkung, dikonsumsi keluarga.',
        ]);

        Harvest::query()->create([
            'user_id' => $user->id,
            'planting_cycle_id' => $pakcoy->id,
            'harvest_date' => $this->inThisMonth(6)->toDateString(),
            'quantity' => 3.0,
            'unit' => 'kg',
            'grade' => 'A',
            'usage_type' => 'konsumsi',
            'notes' => 'Panen selingan pakcoy dari lubang yang sudah padat.',
        ]);

        $nutrisi = ExpenseCategory::query()->where('code', 'nutrisi')->firstOrFail();
        $benih = ExpenseCategory::query()->where('code', 'benih')->firstOrFail();

        Expense::query()->create([
            'user_id' => $user->id,
            'garden_id' => $garden->id,
            'planting_cycle_id' => $pakcoy->id,
            'expense_category_id' => $nutrisi->id,
            'expense_date' => $this->inThisMonth(4)->toDateString(),
            'description' => 'Beli nutrisi AB Mix',
            'quantity' => 1,
            'unit' => 'paket',
            'unit_price' => 42000,
            'total' => 42000,
        ]);

        Expense::query()->create([
            'user_id' => $user->id,
            'garden_id' => $garden->id,
            'expense_category_id' => $benih->id,
            'expense_date' => $this->inThisMonth(12)->toDateString(),
            'description' => 'Benih pakcoy dan media semai',
            'quantity' => 1,
            'unit' => 'paket',
            'unit_price' => 44500,
            'total' => 44500,
        ]);

        PlantIssue::query()->create([
            'user_id' => $user->id,
            'planting_cycle_id' => $tomat->id,
            'discovered_at' => $this->inThisMonth(5)->setTime(9, 0),
            'symptom_category' => 'daun',
            'affected_part' => 'daun',
            'severity' => 'sedang',
            'description' => 'Daun tomat mulai menguning.',
            'action_taken' => 'Kurangi siraman dan cek drainase polybag.',
            'status' => 'monitoring',
        ]);
    }

    private function activate(
        AgendaGeneratorService $agenda,
        User $user,
        Garden $garden,
        string $slug,
        string $methodCode,
        string $name,
        int $daysAgo,
        float $quantity,
        string $unit,
    ): PlantingCycle {
        $commodity = Commodity::query()->where('slug', $slug)->firstOrFail();
        $method = CultivationMethod::query()->where('code', $methodCode)->firstOrFail();
        $template = CultivationTemplate::query()->where('commodity_id', $commodity->id)->firstOrFail();

        $cycle = PlantingCycle::query()->create([
            'user_id' => $user->id,
            'garden_id' => $garden->id,
            'commodity_id' => $commodity->id,
            'template_id' => $template->id,
            'cultivation_method_id' => $method->id,
            'name' => $name,
            'start_date' => now()->subDays($daysAgo)->toDateString(),
            'quantity' => $quantity,
            'quantity_unit' => $unit,
            'status' => 'draft',
        ]);

        return $agenda->activate($cycle);
    }

    private function inThisMonth(int $daysAgo): Carbon
    {
        $date = now()->subDays($daysAgo)->startOfDay();

        if ($date->month !== now()->month || $date->year !== now()->year) {
            return now()->startOfMonth();
        }

        return $date;
    }
}
