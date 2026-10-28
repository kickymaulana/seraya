<?php

namespace Tests\Feature;

use App\Models\Handover;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_route_is_available_for_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/')
            ->assertOk()
            ->assertSee('id="app"', false);
    }

    public function test_dashboard_shows_handover_summary_and_item_categories(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $itemType = ItemType::factory()->create(['category_id' => null]);
        Handover::factory()->create(['status' => 'SELESAI']);
        Handover::factory()->create(['status' => 'MENUNGGU_KONFIRMASI']);

        $this->actingAs($user)
            ->get('/')
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('summary.total', 2)
                ->where('summary.itemTypes', 1)
                ->where('summary.completed', 1)
                ->where('summary.pending', 1)
                ->where('categoryBreakdown.0.name', 'Tanpa kategori'));
    }
}
