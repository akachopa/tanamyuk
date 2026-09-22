<?php

namespace Database\Seeders;

use App\Models\Commodity;
use App\Models\CultivationMethod;
use App\Models\CultivationTemplate;
use App\Models\TemplateStage;
use App\Models\TemplateTask;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed('pakcoy', 'hidroponik', [
            'name' => 'Pakcoy hidroponik 30 hari',
            'duration_days' => 30,
            'difficulty_level' => 'mudah',
            'description' => 'Dari semai sampai panen pakcoy di instalasi hidroponik kecil.',
        ], [
            ['Persemaian', 'Benih disemai di media lembab.', 0, 6, [
                ['semai', 'Semai benih pakcoy', 0, 20, 'Sebar benih tipis di rockwool atau media semai.'],
                ['siram', 'Jaga media semai tetap lembab', 0, 5, 'Cek pagi dan sore. Jangan sampai menggenang.', 2, 6],
            ]],
            ['Pindah tanam', 'Pindahkan bibit ke netpot.', 7, 13, [
                ['tanam', 'Pindah ke instalasi hidroponik', 7, 30, 'Pilih bibit berdaun 3–4. Isi netpot dengan media.'],
                ['pupuk', 'Isi larutan nutrisi awal', 7, 15, 'Campur nutrisi sesuai takaran pemula.'],
            ]],
            ['Pembesaran', 'Daun melebar dan rumpun padat.', 14, 26, [
                ['pupuk', 'Cek larutan nutrisi', 14, 10, 'Ukur kepekatan dan tambah air bila perlu.'],
                ['siram', 'Cek aliran air', 14, 5, 'Pastikan pompa dan selang mengalir.', 2, 26],
                ['cek', 'Cek hama pada daun', 18, 10, 'Periksa bawah daun. Angkat daun yang rusak.'],
            ]],
            ['Panen', 'Potong pangkal saat krop padat.', 27, 29, [
                ['panen', 'Panen pakcoy', 29, 20, 'Potong di atas akar bila ingin tumbuh lagi, atau cabut seluruh tanaman.'],
            ]],
        ]);

        $this->seed('cabai-rawit', 'pot', [
            'name' => 'Cabai rawit polybag',
            'duration_days' => 80,
            'difficulty_level' => 'sedang',
            'description' => 'Budidaya cabai rawit di polybag sampai panen pertama.',
        ], [
            ['Persemaian', 'Benih cabai butuh semai yang hangat.', 0, 13, [
                ['semai', 'Semai benih cabai rawit', 0, 20, 'Rendam benih, lalu semai di tray.'],
                ['siram', 'Siram semai cabai', 0, 5, 'Siram tipis agar media tidak becek.', 2, 13],
            ]],
            ['Pindah tanam', 'Satu bibit untuk satu polybag.', 14, 27, [
                ['tanam', 'Pindah ke polybag', 14, 30, 'Gunakan media gembur. Tanam sejajar daun lembaga.'],
                ['pupuk', 'Pupuk dasar', 14, 15, 'Campurkan kompos matang ke media.'],
            ]],
            ['Vegetatif', 'Batang meninggi dan bercabang.', 28, 44, [
                ['siram', 'Siram cabai rawit', 28, 10, 'Siram ke media, hindari membasahi daun sore hari.', 3, 44],
                ['cek', 'Cek kutu daun', 40, 10, 'Semprot air atau rapikan daun yang menggulung.'],
            ]],
            ['Pembungaan', 'Bunga muncul dan buah mengikat.', 45, 69, [
                ['pupuk', 'Pupuk susulan pembungaan', 46, 15, 'Beri nutrisi yang mendukung bunga dan buah.'],
                ['siram', 'Siram saat berbunga', 46, 10, 'Jaga media lembab, jangan kekeringan.', 3, 69],
                ['cek', 'Cek bunga dan buah muda', 55, 10, 'Hitung buah yang mengikat dan buang yang busuk.'],
            ]],
            ['Panen', 'Petik cabai yang sudah merah.', 70, 79, [
                ['panen', 'Panen perdana cabai rawit', 79, 20, 'Petik dengan tangkai. Sisakan buah muda.'],
                ['siram', 'Siram setelah panen', 76, 10, 'Lanjutkan siram agar tanaman tetap berbuah.', 3, 79],
            ]],
        ]);

        $this->seed('tomat', 'pot', [
            'name' => 'Tomat polybag',
            'duration_days' => 75,
            'difficulty_level' => 'sedang',
            'description' => 'Tomat di polybag dengan ajir dan pemangkasan.',
        ], [
            ['Persemaian', 'Semai sampai bibit kekar.', 0, 13, [
                ['semai', 'Semai benih tomat', 0, 20, 'Semai dangkal dan jaga tetap hangat.'],
                ['siram', 'Siram semai tomat', 0, 5, 'Siram pagi secukupnya.', 2, 13],
            ]],
            ['Pindah tanam', 'Pindah saat bibit berdaun sejati.', 14, 24, [
                ['tanam', 'Pindah tomat ke polybag', 14, 30, 'Tanam agak dalam agar batang kokoh.'],
                ['pupuk', 'Pupuk dasar tomat', 14, 15, 'Campur kompos ke media tanam.'],
            ]],
            ['Vegetatif', 'Tunas air mulai muncul.', 25, 49, [
                ['rawat', 'Pasang ajir', 26, 15, 'Ikat batang longgar ke ajir.'],
                ['pupuk', 'Pupuk pertumbuhan', 30, 15, 'Beri pupuk sesuai dosis polybag.'],
                ['rawat', 'Pangkas tunas air', 34, 15, 'Buang tunas di ketiak daun agar energi ke buah.'],
                ['siram', 'Siram tomat', 28, 10, 'Siram langsung ke media.', 3, 49],
                ['cek', 'Cek daun menguning', 40, 10, 'Daun bawah yang kuning bisa dipangkas.'],
            ]],
            ['Pembungaan', 'Jaga buah muda tetap sehat.', 50, 69, [
                ['siram', 'Siram saat berbuah', 52, 10, 'Jangan biarkan media kering mendadak.', 3, 69],
                ['pupuk', 'Pupuk buah', 55, 15, 'Tambah nutrisi kalium ringan.'],
            ]],
            ['Panen', 'Petik saat warna merata.', 70, 74, [
                ['panen', 'Panen tomat', 74, 20, 'Petik yang sudah berubah warna. Jangan tarik batangnya.'],
            ]],
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<int, array{0: string, 1: string, 2: int, 3: int, 4: array<int, array<int, mixed>>}>  $stages
     */
    private function seed(string $slug, string $methodCode, array $meta, array $stages): void
    {
        $commodity = Commodity::query()->where('slug', $slug)->firstOrFail();
        $method = CultivationMethod::query()->where('code', $methodCode)->firstOrFail();

        $template = CultivationTemplate::query()->create([
            'commodity_id' => $commodity->id,
            'cultivation_method_id' => $method->id,
            'name' => $meta['name'],
            'version' => '1',
            'duration_days' => $meta['duration_days'],
            'difficulty_level' => $meta['difficulty_level'],
            'description' => $meta['description'],
            'requirements' => [
                'sunlight_min_hours' => $commodity->sunlight_min_hours,
                'sunlight_max_hours' => $commodity->sunlight_max_hours,
                'min_space_m2' => (float) $commodity->min_space_m2,
                'water_need_level' => $commodity->water_need_level,
                'maintenance_minutes_per_day' => $commodity->maintenance_minutes_per_day,
                'difficulty_level' => $commodity->difficulty_level,
            ],
            'status' => 'published',
            'published_at' => now(),
        ]);

        foreach ($stages as $index => $stage) {
            [$name, $description, $start, $end, $tasks] = $stage;

            $stageModel = TemplateStage::query()->create([
                'template_id' => $template->id,
                'name' => $name,
                'description' => $description,
                'start_day_offset' => $start,
                'end_day_offset' => $end,
                'sort_order' => $index,
            ]);

            foreach ($tasks as $taskIndex => $task) {
                TemplateTask::query()->create([
                    'template_id' => $template->id,
                    'stage_id' => $stageModel->id,
                    'task_type' => $task[0],
                    'title' => $task[1],
                    'first_day_offset' => $task[2],
                    'estimated_minutes' => $task[3],
                    'instruction' => $task[4],
                    'repeat_interval_days' => $task[5] ?? null,
                    'repeat_until_day_offset' => $task[6] ?? null,
                    'is_required' => true,
                    'sort_order' => $taskIndex,
                ]);
            }
        }
    }
}
