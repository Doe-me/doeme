<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private array $categoryData = [
        'name' => 'Nova Categoria',
        'description' => 'Descrição da nova categoria',
        'icon' => 'new-icon',
    ];

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/categories', $this->categoryData);

        $response->assertStatus(201);
    }

    public function test_non_admin_cannot_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/categories', $this->categoryData);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/categories/{$category->id}", ['name' => 'Categoria Atualizada']);

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_update_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/categories/{$category->id}", ['name' => 'Categoria Atualizada']);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_delete_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(403);
    }
}
