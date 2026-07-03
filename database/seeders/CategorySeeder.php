<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Móveis',
                'description' => 'Móveis para casa, escritório e decoração',
                'icon' => 'furniture',
            ],
            [
                'name' => 'Roupas',
                'description' => 'Roupas, calçados e acessórios',
                'icon' => 'clothing',
            ],
            [
                'name' => 'Eletrônicos',
                'description' => 'Aparelhos eletrônicos, celulares e computadores',
                'icon' => 'electronics',
            ],
            [
                'name' => 'Livros',
                'description' => 'Livros, revistas e materiais de estudo',
                'icon' => 'books',
            ],
            [
                'name' => 'Brinquedos',
                'description' => 'Brinquedos e jogos infantis',
                'icon' => 'toys',
            ],
            [
                'name' => 'Utensílios Domésticos',
                'description' => 'Utensílios de cozinha e casa',
                'icon' => 'kitchen',
            ],
            [
                'name' => 'Esportes',
                'description' => 'Equipamentos esportivos e de exercício',
                'icon' => 'sports',
            ],
            [
                'name' => 'Decoração',
                'description' => 'Itens de decoração e arte',
                'icon' => 'decoration',
            ],
            [
                'name' => 'Instrumentos Musicais',
                'description' => 'Instrumentos musicais e equipamentos de som',
                'icon' => 'music',
            ],
            [
                'name' => 'Outros',
                'description' => 'Outros itens diversos',
                'icon' => 'other',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'active' => true,
            ]);
        }
    }
}
