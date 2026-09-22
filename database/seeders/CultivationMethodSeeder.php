<?php

namespace Database\Seeders;

use App\Models\CultivationMethod;
use Illuminate\Database\Seeder;

class CultivationMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => 'tanah', 'name' => 'Tanah', 'description' => 'Tanam langsung di bedengan atau lahan.'],
            ['code' => 'pot', 'name' => 'Polybag', 'description' => 'Tanam di pot, polybag, atau wadah sejenis.'],
            ['code' => 'hidroponik', 'name' => 'Hidroponik', 'description' => 'Tanam dengan larutan nutrisi, tanpa tanah.'],
        ];

        foreach ($methods as $method) {
            CultivationMethod::query()->create([
                ...$method,
                'status' => 'active',
            ]);
        }
    }
}
