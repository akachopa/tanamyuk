<?php

namespace Database\Seeders;

use App\Models\Commodity;
use App\Models\CommodityCategory;
use App\Models\CultivationMethod;
use App\Support\PivotSync;
use Illuminate\Database\Seeder;

class CommoditySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'sayuran-daun' => CommodityCategory::query()->create([
                'name' => 'Sayuran daun',
                'slug' => 'sayuran-daun',
                'sort_order' => 1,
                'status' => 'active',
            ]),
            'buah' => CommodityCategory::query()->create([
                'name' => 'Buah',
                'slug' => 'buah',
                'sort_order' => 2,
                'status' => 'active',
            ]),
            'bumbu' => CommodityCategory::query()->create([
                'name' => 'Bumbu',
                'slug' => 'bumbu',
                'sort_order' => 3,
                'status' => 'active',
            ]),
        ];

        $methods = CultivationMethod::query()->pluck('id', 'code');

        $rows = [
            ['sayuran-daun', 'Kangkung', 'kangkung', 'Ipomoea aquatica', 'Sayuran air yang cepat dipanen.', 'mudah', 0.4, 3, 8, 'tinggi', 10, 21, 30, true, ['tanah' => 85, 'pot' => 90, 'hidroponik' => 90]],
            ['sayuran-daun', 'Bayam', 'bayam', 'Amaranthus tricolor', 'Sayuran daun untuk konsumsi harian.', 'mudah', 0.4, 4, 8, 'sedang', 10, 25, 35, true, ['tanah' => 90, 'pot' => 85, 'hidroponik' => 75]],
            ['sayuran-daun', 'Pakcoy', 'pakcoy', 'Brassica rapa subsp. chinensis', 'Siklus pendek dan rapi di hidroponik atau pot.', 'mudah', 0.5, 4, 7, 'sedang', 15, 30, 40, false, ['tanah' => 70, 'pot' => 88, 'hidroponik' => 95]],
            ['sayuran-daun', 'Sawi', 'sawi', 'Brassica juncea', 'Tumbuh cepat di lahan kecil.', 'mudah', 0.5, 4, 7, 'sedang', 12, 30, 40, false, ['tanah' => 88, 'pot' => 84, 'hidroponik' => 80]],
            ['sayuran-daun', 'Selada', 'selada', 'Lactuca sativa', 'Nyaman di tempat teduh sebagian.', 'sedang', 0.4, 3, 6, 'tinggi', 15, 35, 50, false, ['tanah' => 75, 'pot' => 85, 'hidroponik' => 92]],
            ['buah', 'Cabai Rawit', 'cabai-rawit', 'Capsicum frutescens', 'Sekali tanam bisa dipetik berkali-kali.', 'sedang', 1.5, 6, 10, 'sedang', 15, 75, 90, true, ['tanah' => 90, 'pot' => 95, 'hidroponik' => 60]],
            ['buah', 'Tomat', 'tomat', 'Solanum lycopersicum', 'Butuh cahaya cukup dan pemangkasan rutin.', 'sedang', 2.0, 6, 10, 'sedang', 25, 70, 90, false, ['tanah' => 88, 'pot' => 92]],
            ['buah', 'Terong', 'terong', 'Solanum melongena', 'Tanaman buah yang rajin dipetik.', 'sedang', 1.5, 6, 9, 'sedang', 15, 70, 90, true, ['tanah' => 90, 'pot' => 86]],
            ['buah', 'Timun', 'timun', 'Cucumis sativus', 'Merambat dan suka air.', 'mudah', 1.2, 6, 9, 'tinggi', 12, 40, 55, true, ['tanah' => 90, 'pot' => 80]],
            ['bumbu', 'Daun Bawang', 'daun-bawang', 'Allium fistulosum', 'Bisa dipanen bertahap dari rumpun.', 'mudah', 0.2, 4, 8, 'sedang', 8, 50, 70, true, ['tanah' => 90, 'pot' => 88]],
            ['bumbu', 'Seledri', 'seledri', 'Apium graveolens', 'Suka media lembab dan cahaya sedang.', 'sedang', 0.3, 3, 6, 'tinggi', 10, 60, 80, true, ['tanah' => 80, 'pot' => 86, 'hidroponik' => 84]],
            ['bumbu', 'Kemangi', 'kemangi', 'Ocimum basilicum', 'Bumbu daun yang muat di pot kecil.', 'mudah', 0.2, 4, 8, 'sedang', 8, 30, 45, true, ['tanah' => 88, 'pot' => 92]],
            ['bumbu', 'Jahe', 'jahe', 'Zingiber officinale', 'Umbi yang butuh waktu, perawatannya ringan.', 'sedang', 0.5, 3, 6, 'sedang', 8, 240, 270, false, ['tanah' => 92, 'pot' => 80]],
            ['bumbu', 'Kunyit', 'kunyit', 'Curcuma longa', 'Umbi dapur yang tahan di pekarangan.', 'mudah', 0.5, 4, 7, 'sedang', 8, 240, 300, false, ['tanah' => 92, 'pot' => 78]],
            ['bumbu', 'Serai', 'serai', 'Cymbopogon citratus', 'Rumpun bumbu yang hemat air.', 'mudah', 0.4, 6, 10, 'rendah', 5, 90, 120, true, ['tanah' => 94, 'pot' => 82]],
        ];

        foreach ($rows as $row) {
            [$category, $name, $slug, $scientific, $description, $difficulty, $space, $sunMin, $sunMax, $water, $minutes, $harvestMin, $harvestMax, $repeat, $methodScores] = $row;

            $commodity = Commodity::query()->create([
                'category_id' => $categories[$category]->id,
                'name' => $name,
                'slug' => $slug,
                'scientific_name' => $scientific,
                'short_description' => $description,
                'difficulty_level' => $difficulty,
                'min_space_m2' => $space,
                'sunlight_min_hours' => $sunMin,
                'sunlight_max_hours' => $sunMax,
                'water_need_level' => $water,
                'maintenance_minutes_per_day' => $minutes,
                'harvest_min_days' => $harvestMin,
                'harvest_max_days' => $harvestMax,
                'repeat_harvest' => $repeat,
                'default_harvest_unit' => 'kg',
                'status' => 'active',
            ]);

            foreach ($methodScores as $code => $score) {
                PivotSync::attach($commodity->cultivationMethods(), $methods[$code], [
                    'suitability_score' => $score,
                ]);
            }
        }
    }
}
