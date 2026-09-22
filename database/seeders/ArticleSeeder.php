<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Commodity;
use App\Support\PivotSync;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $category = ArticleCategory::query()->create([
            'name' => 'Pertanyaan umum',
            'slug' => 'faq',
            'type' => 'faq',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $articles = [
            ['Apakah TanamYuk bisa digunakan tanpa internet?', 'apakah-tanamyuk-bisa-offline', 'Agenda, jurnal, dan panduan yang sudah terbuka tetap bisa dibaca. Perubahan dikirim saat koneksi kembali.'],
            ['Kapan waktu terbaik menyiram tanaman?', 'waktu-terbaik-menyiram', 'Umumnya pagi sebelum matahari terik, atau sore saat suhu turun. Siram ke media, bukan ke daun.'],
            ['Bagaimana mengetahui jadwal pemupukan?', 'jadwal-pemupukan', 'Saat siklus diaktifkan, tugas pupuk disalin dari template beserta tanggalnya. Lihat di agenda.'],
            ['Bagaimana membayar paket Plus?', 'membayar-paket-plus', 'Pilih paket Plus, buat pesanan, lalu selesaikan pembayaran. Aktivasi otomatis menyusul setelah pembayaran terverifikasi.'],
            ['Bagaimana mencatat hasil panen?', 'mencatat-hasil-panen', 'Buka catatan, pilih panen, lalu isi siklus, jumlah, satuan, dan apakah hasilnya dikonsumsi atau dijual.'],
            ['Tanaman apa yang cocok untuk pemula?', 'tanaman-untuk-pemula', 'Kangkung, bayam, dan pakcoy biasanya paling mudah: siklusnya pendek dan toleran jika jadwalmu padat.'],
            ['Bagaimana memulai kebun pertama?', 'memulai-kebun-pertama', 'Buat satu kebun, catat luas dan jam matahari, lalu aktifkan satu siklus. Agenda harian akan terisi dari template.'],
            ['Apa beda paket Gratis dan Plus?', 'beda-gratis-dan-plus', 'Gratis cukup untuk 1 kebun dan 3 siklus aktif. Plus membuka lebih banyak kebun, siklus, dan catatan yang lebih lengkap.'],
        ];

        foreach ($articles as $index => [$title, $slug, $summary]) {
            Article::query()->create([
                'category_id' => $category->id,
                'title' => $title,
                'slug' => $slug,
                'summary' => $summary,
                'content' => $summary.' Simpan catatanmu secara rutin supaya keputusan siram, pupuk, dan panen tidak bergantung pada ingatan saja.',
                'reading_minutes' => 2,
                'access_level' => 'free',
                'version' => '1',
                'status' => 'published',
                'published_at' => now()->subDays(count($articles) - $index),
            ]);
        }

        $pakcoyArticle = Article::query()->where('slug', 'tanaman-untuk-pemula')->first();
        $pakcoy = Commodity::query()->where('slug', 'pakcoy')->first();

        if ($pakcoyArticle && $pakcoy) {
            PivotSync::attach($pakcoyArticle->commodities(), $pakcoy->id);
        }
    }
}
