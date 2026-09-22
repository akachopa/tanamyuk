<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'benih', 'name' => 'Benih dan bibit', 'sort_order' => 1],
            ['code' => 'media', 'name' => 'Media tanam', 'sort_order' => 2],
            ['code' => 'nutrisi', 'name' => 'Nutrisi dan pupuk', 'sort_order' => 3],
            ['code' => 'pot', 'name' => 'Pot dan wadah', 'sort_order' => 4],
            ['code' => 'alat', 'name' => 'Alat berkebun', 'sort_order' => 5],
            ['code' => 'perlindungan', 'name' => 'Perlindungan tanaman', 'sort_order' => 6],
            ['code' => 'lain', 'name' => 'Lain-lain', 'sort_order' => 7],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::query()->create([
                ...$category,
                'status' => 'active',
            ]);
        }
    }
}
