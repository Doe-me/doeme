<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;
use App\Models\User;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories()
    {
        // Criar algumas categorias
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'icon',
                            'active',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }

    public function test_can_show_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'icon',
                        'active',
                        'created_at',
                        'updated_at'
                    ]
                ]);
    }

    public function test_authenticated_user_can_create_category()
    {
        $user = User::factory()->create();

        $categoryData = [
            'name' => 'Nova Categoria',
            'description' => 'Descrição da nova categoria',
            'icon' => 'new-icon'
        ];

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'icon',
                        'active',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Nova Categoria',
            'slug' => 'nova-categoria'
        ]);
    }

    public function test_unauthenticated_user_cannot_create_category()
    {
        $categoryData = [
            'name' => 'Nova Categoria',
            'description' => 'Descrição da nova categoria'
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(401);
    }

    public function test_category_creation_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/categories', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }
}

