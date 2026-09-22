<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use App\Support\PivotSync;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            'max_active_gardens' => ['Jumlah kebun aktif', 'number', 'Batas kebun dengan status aktif.'],
            'max_active_cycles' => ['Jumlah siklus aktif', 'number', 'Batas siklus tanam yang sedang berjalan.'],
            'journal_photos' => ['Foto jurnal', 'boolean', 'Unggah foto pada catatan jurnal.'],
            'offline_sync' => ['Sinkronisasi offline', 'boolean', 'Simpan perubahan saat tidak ada internet.'],
            'priority_support' => ['Dukungan prioritas', 'boolean', 'Bantuan lebih cepat untuk pelanggan Plus.'],
            'marketing_highlights' => ['Sorotan paket', 'text', 'Daftar singkat fitur yang ditampilkan di halaman paket.'],
        ];

        $featureModels = [];
        foreach ($features as $code => [$name, $type, $description]) {
            $featureModels[$code] = Feature::query()->create([
                'code' => $code,
                'name' => $name,
                'description' => $description,
                'value_type' => $type,
            ]);
        }

        $this->makePlan(
            [
                'code' => 'free',
                'name' => 'Gratis',
                'description' => 'Mulai menanam tanpa biaya.',
                'billing_period' => 'lifetime',
                'duration_days' => 36500,
                'price' => 0,
                'is_free' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            $featureModels,
            limits: [1, 3],
            flags: [false, false, false],
            highlights: ['1 lahan', '3 siklus aktif', 'Agenda dasar'],
        );

        $this->makePlan(
            [
                'code' => 'plus_monthly',
                'name' => 'Plus Bulanan',
                'description' => 'Pengingat dan catatan yang lebih lengkap.',
                'billing_period' => 'monthly',
                'duration_days' => 30,
                'price' => 29000,
                'is_free' => false,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            $featureModels,
            limits: [null, null],
            flags: [true, true, false],
            highlights: ['Lahan tanpa batas', 'Semua siklus', 'Catatan biaya dan panen'],
        );

        $this->makePlan(
            [
                'code' => 'plus_yearly',
                'name' => 'Plus Tahunan',
                'description' => 'Hemat untuk yang menanam sepanjang tahun.',
                'billing_period' => 'yearly',
                'duration_days' => 365,
                'price' => 249000,
                'is_free' => false,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            $featureModels,
            limits: [null, null],
            flags: [true, true, true],
            highlights: ['Semua fitur Plus', 'Prioritas bantuan', 'Setara Rp20.750/bulan'],
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, Feature>  $features
     * @param  array{0: int|null, 1: int|null}  $limits
     * @param  array{0: bool, 1: bool, 2: bool}  $flags
     * @param  array<int, string>  $highlights
     */
    private function makePlan(array $attributes, array $features, array $limits, array $flags, array $highlights): void
    {
        $plan = Plan::query()->create([
            ...$attributes,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        $this->attachNumber($plan, $features['max_active_gardens'], $limits[0]);
        $this->attachNumber($plan, $features['max_active_cycles'], $limits[1]);
        $this->attachBoolean($plan, $features['journal_photos'], $flags[0]);
        $this->attachBoolean($plan, $features['offline_sync'], $flags[1]);
        $this->attachBoolean($plan, $features['priority_support'], $flags[2]);

        PivotSync::attach($plan->features(), $features['marketing_highlights']->id, [
            'value_boolean' => null,
            'value_number' => null,
            'value_text' => json_encode($highlights, JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function attachNumber(Plan $plan, Feature $feature, ?int $value): void
    {
        PivotSync::attach($plan->features(), $feature->id, [
            'value_boolean' => null,
            'value_number' => $value,
            'value_text' => $value === null ? 'unlimited' : null,
        ]);
    }

    private function attachBoolean(Plan $plan, Feature $feature, bool $value): void
    {
        PivotSync::attach($plan->features(), $feature->id, [
            'value_boolean' => $value,
            'value_number' => null,
            'value_text' => null,
        ]);
    }
}
