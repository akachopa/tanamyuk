<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            CultivationMethodSeeder::class,
            CommoditySeeder::class,
            TemplateSeeder::class,
            RecommendationRuleSeeder::class,
            ExpenseCategorySeeder::class,
            ArticleSeeder::class,
            DemoUserSeeder::class,
        ]);
    }
}
