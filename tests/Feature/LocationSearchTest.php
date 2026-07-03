<?php

namespace Tests\Feature;

use App\Models\DonationItem;
use App\Repositories\DonationItemRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationSearchTest extends TestCase
{
    use RefreshDatabase;

    // Centro de referência: Praça da Sé, São Paulo
    private float $centerLat = -23.5505;

    private float $centerLon = -46.6333;

    public function test_items_inside_radius_are_returned(): void
    {
        // ~1 km de distância (Liberdade, SP)
        $item = DonationItem::factory()->withCoordinates(-23.5580, -46.6324)->create();

        $repo = app(DonationItemRepository::class);
        $result = $repo->findByLocation($this->centerLat, $this->centerLon, radius: 10);

        $this->assertTrue($result->pluck('id')->contains($item->id));
    }

    public function test_items_outside_radius_are_not_returned(): void
    {
        // ~350 km de distância (Campinas, SP)
        $item = DonationItem::factory()->withCoordinates(-22.9099, -47.0626)->create();

        $repo = app(DonationItemRepository::class);
        $result = $repo->findByLocation($this->centerLat, $this->centerLon, radius: 10);

        $this->assertFalse($result->pluck('id')->contains($item->id));
    }

    public function test_results_are_ordered_by_distance_ascending(): void
    {
        // ~1 km (Liberdade)
        $near = DonationItem::factory()->withCoordinates(-23.5580, -46.6324)->create();
        // ~5 km (Pinheiros)
        $far = DonationItem::factory()->withCoordinates(-23.5676, -46.6932)->create();

        $repo = app(DonationItemRepository::class);
        $ids = $repo->findByLocation($this->centerLat, $this->centerLon, radius: 10)
            ->pluck('id')
            ->filter(fn ($id) => in_array($id, [$near->id, $far->id]))
            ->values()
            ->all();

        $this->assertEquals([$near->id, $far->id], $ids);
    }

    public function test_items_without_coordinates_are_excluded(): void
    {
        $item = DonationItem::factory()->create(['latitude' => null, 'longitude' => null]);

        $repo = app(DonationItemRepository::class);
        $result = $repo->findByLocation($this->centerLat, $this->centerLon, radius: 10);

        $this->assertFalse($result->pluck('id')->contains($item->id));
    }

    public function test_donated_items_are_excluded(): void
    {
        $item = DonationItem::factory()->withCoordinates(-23.5580, -46.6324)->donated()->create();

        $repo = app(DonationItemRepository::class);
        $result = $repo->findByLocation($this->centerLat, $this->centerLon, radius: 10);

        $this->assertFalse($result->pluck('id')->contains($item->id));
    }

    public function test_endpoint_filters_by_lat_lon_radius(): void
    {
        $inside = DonationItem::factory()->withCoordinates(-23.5580, -46.6324)->create();
        $outside = DonationItem::factory()->withCoordinates(-22.9099, -47.0626)->create();

        $response = $this->getJson('/api/donation-items?'.http_build_query([
            'latitude' => $this->centerLat,
            'longitude' => $this->centerLon,
            'radius' => 10,
        ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($inside->id));
        $this->assertFalse($ids->contains($outside->id));
    }

    public function test_scope_near_location_uses_bounding_box(): void
    {
        $inside = DonationItem::factory()->withCoordinates(-23.5580, -46.6324)->create();
        $outside = DonationItem::factory()->withCoordinates(-22.9099, -47.0626)->create();

        $results = DonationItem::nearLocation($this->centerLat, $this->centerLon, 10)->pluck('id');

        $this->assertTrue($results->contains($inside->id));
        $this->assertFalse($results->contains($outside->id));
    }
}
