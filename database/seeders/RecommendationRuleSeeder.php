<?php

namespace Database\Seeders;

use App\Models\Commodity;
use App\Models\CommodityCategory;
use App\Models\RecommendationRule;
use Illuminate\Database\Seeder;

class RecommendationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $category = CommodityCategory::query()->pluck('id', 'slug');
        $commodity = Commodity::query()->pluck('id', 'slug');

        $rules = [
            ['Pemula dan sayuran daun', 'experience_level', 'eq', 'pemula', null, 'sayuran-daun', 6, 'Ramah untuk yang baru mulai.', 20],
            ['Cahaya untuk tanaman buah', 'sunlight_hours', 'gte', '6', null, 'buah', 6, 'Cahaya cukup untuk tanaman buah.', 15],
            ['Waktu singkat untuk daun', 'care_minutes', 'lte', '20', null, 'sayuran-daun', 3, 'Waktu perawatan harian masih cukup.', 10],
            ['Konsumsi sayuran daun', 'goal', 'eq', 'konsumsi', null, 'sayuran-daun', 4, 'Cepat dipanen untuk konsumsi rumah.', 12],
            ['Hemat belanja', 'goal', 'eq', 'hemat', null, 'sayuran-daun', 4, 'Sayuran daun membantu menghemat belanja.', 12],
            ['Pakcoy hidroponik', 'method', 'eq', 'hidroponik', 'pakcoy', null, 5, 'Pakcoy tumbuh rapi di hidroponik.', 18],
            ['Cabai di polybag', 'method', 'eq', 'polybag', 'cabai-rawit', null, 4, 'Cabai rawit cocok di polybag.', 14],
            ['Kangkung suka air', 'water', 'eq', 'tinggi', 'kangkung', null, 4, 'Kangkung menyukai air yang mudah didapat.', 16],
            ['Preferensi daun', 'preference', 'eq', 'daun', null, 'sayuran-daun', 4, 'Sesuai pilihan sayuran daun.', 8],
            ['Lahan sempit kemangi', 'area_m2', 'lte', '2', 'kemangi', null, 3, 'Kemangi muat di lahan sempit.', 8],
        ];

        foreach ($rules as [$name, $field, $operator, $value, $commoditySlug, $categorySlug, $delta, $reason, $priority]) {
            RecommendationRule::query()->create([
                'name' => $name,
                'input_field' => $field,
                'operator' => $operator,
                'comparison_value' => $value,
                'commodity_id' => $commoditySlug ? $commodity[$commoditySlug] : null,
                'category_id' => $categorySlug ? $category[$categorySlug] : null,
                'score_delta' => $delta,
                'reason_template' => $reason,
                'priority' => $priority,
                'status' => 'active',
            ]);
        }
    }
}
