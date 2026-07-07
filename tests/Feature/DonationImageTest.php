<?php

namespace Tests\Feature;

use App\Models\DonationImages;
use App\Models\DonationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DonationImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_valid_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/donation-items/{$item->id}/images", [
            'images' => [UploadedFile::fake()->image('photo.png')],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('donation_images', ['donation_item_id' => $item->id]);
    }

    public function test_upload_non_image_file_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/donation-items/{$item->id}/images", [
            'images' => [UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')],
        ]);

        $response->assertStatus(422);
    }

    public function test_deleting_item_removes_image_records(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        // Upload image first
        $this->withToken($token)->postJson("/api/donation-items/{$item->id}/images", [
            'images' => [UploadedFile::fake()->image('photo.png')],
        ]);

        $this->assertDatabaseHas('donation_images', ['donation_item_id' => $item->id]);

        // Delete item
        $this->withToken($token)->deleteJson("/api/donation-items/{$item->id}");

        $this->assertDatabaseMissing('donation_images', ['donation_item_id' => $item->id]);
    }

    public function test_show_returns_uploaded_images_with_url(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson("/api/donation-items/{$item->id}/images", [
            'images' => [UploadedFile::fake()->image('photo.png')],
        ]);

        $response = $this->getJson("/api/donation-items/{$item->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.donation_images.0.donation_item_id', $item->id)
            ->assertJsonStructure([
                'data' => [
                    'donation_images' => [['id', 'path', 'url']],
                ],
            ]);
    }

    public function test_owner_can_delete_single_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        $upload = $this->withToken($token)->postJson("/api/donation-items/{$item->id}/images", [
            'images' => [UploadedFile::fake()->image('photo.png')],
        ]);
        $imageId = $upload->json('data.0.id');

        $response = $this->withToken($token)
            ->deleteJson("/api/donation-items/{$item->id}/images/{$imageId}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('donation_images', ['id' => $imageId]);
    }

    public function test_non_owner_cannot_delete_image(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $item = DonationItem::factory()->create(['user_id' => $owner->id]);
        // Seed direto para isolar a única requisição HTTP (autenticada como intruso).
        $image = DonationImages::create([
            'donation_item_id' => $item->id,
            'path' => "donations/{$item->id}/photo.png",
        ]);
        $intruderToken = $intruder->createToken('test')->plainTextToken;

        $response = $this->withToken($intruderToken)
            ->deleteJson("/api/donation-items/{$item->id}/images/{$image->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('donation_images', ['id' => $image->id]);
    }

    public function test_cannot_delete_image_that_belongs_to_another_item(): void
    {
        $user = User::factory()->create();
        $itemA = DonationItem::factory()->create(['user_id' => $user->id]);
        $itemB = DonationItem::factory()->create(['user_id' => $user->id]);
        $image = DonationImages::create([
            'donation_item_id' => $itemB->id,
            'path' => "donations/{$itemB->id}/photo.png",
        ]);
        $token = $user->createToken('test')->plainTextToken;

        // Tenta apagar via itemA uma imagem que pertence ao itemB
        $response = $this->withToken($token)
            ->deleteJson("/api/donation-items/{$itemA->id}/images/{$image->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('donation_images', ['id' => $image->id]);
    }
}
