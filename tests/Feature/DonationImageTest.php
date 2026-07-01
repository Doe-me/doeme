<?php

namespace Tests\Feature;

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
}
