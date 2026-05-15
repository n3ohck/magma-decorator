<?php

namespace Database\Seeders;

use App\Models\MaterialCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Mármol',
            'Cuarzo',
            'Cuarcita',
            'Granito',
            'Piedra natural',
            'Cerámico',
            'Porcelánico',
        ];

        foreach ($categories as $index => $name) {
            MaterialCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
