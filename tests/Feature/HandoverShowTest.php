<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Handover;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoverShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sender_can_view_handover_detail_with_timeline_and_items(): void
    {
        $sender = User::factory()->create(['name' => 'Pengirim Detail']);
        $receiver = User::factory()->create(['name' => 'Penerima Detail']);
        $itemType = ItemType::factory()->create(['name' => 'Laptop Detail', 'unit' => 'UNIT']);
        $handover = Handover::factory()->create([
            'number' => 'SRY-SHOW-001',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'MENUNGGU_KONFIRMASI_PENERIMA',
            'purpose' => 'Kebutuhan detail',
            'notes' => 'Catatan detail',
            'sender_confirmed_at' => '2026-10-27 08:00:00',
        ]);
        $handover->items()->create([
            'item_type_id' => $itemType->id,
            'quantity' => 2,
            'condition' => 'BAIK',
            'notes' => 'Item detail',
        ]);

        $response = $this->actingAs($sender)->get(route('handovers.show', $handover));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Handovers/Show')
                ->where('handover.id', $handover->id)
                ->where('handover.number', 'SRY-SHOW-001')
                ->where('handover.sender.name', 'Pengirim Detail')
                ->where('handover.items.0.item_type.name', 'Laptop Detail')
                ->where('handover.items.0.notes', 'Item detail')
                ->where('timeline.0.label', 'Transaksi dibuat')
                ->where('timeline.1.label', 'Pengirim mengonfirmasi')
                ->where('timeline.1.completed', true)
                ->where('timeline.2.label', 'Penerima mengonfirmasi')
                ->where('timeline.2.completed', false));
    }

    public function test_receiver_can_view_handover_detail(): void
    {
        $receiver = User::factory()->create();
        $handover = Handover::factory()->create(['receiver_id' => $receiver->id]);

        $this->actingAs($receiver)
            ->get(route('handovers.show', $handover))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Handovers/Show'));
    }

    public function test_unrelated_user_cannot_view_handover_detail(): void
    {
        $handover = Handover::factory()->create();
        $unrelatedUser = User::factory()->create();

        $this->actingAs($unrelatedUser)
            ->get(route('handovers.show', $handover))
            ->assertForbidden();
    }

    public function test_admin_and_operator_can_view_handover_detail(): void
    {
        $handover = Handover::factory()->create();

        foreach (['admin', 'operator'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('handovers.show', $handover))
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('Handovers/Show'));
        }
    }

    public function test_receiver_search_finds_users_by_name_nik_or_department(): void
    {
        $sender = User::factory()->create(['name' => 'Pengirim Search']);
        $department = Department::factory()->create(['name' => 'Produksi']);
        $receiver = User::factory()->create(['name' => 'Budi Search', 'nik' => 'NIK-123']);
        $receiver->departments()->attach($department);
        User::factory()->create(['name' => 'Budi Tidak Aktif', 'active' => false]);

        $this->actingAs($sender)
            ->getJson(route('handovers.receivers.search', ['search' => 'Produksi']))
            ->assertOk()
            ->assertJsonPath('users.0.id', $receiver->id)
            ->assertJsonPath('users.0.departments.0.name', 'Produksi');

        $this->actingAs($sender)
            ->getJson(route('handovers.receivers.search', ['search' => 'NIK-123']))
            ->assertOk()
            ->assertJsonPath('users.0.name', 'Budi Search');
    }

    public function test_item_type_search_finds_active_items_by_name_or_category(): void
    {
        $itemType = ItemType::factory()->create(['name' => 'Laptop Search', 'category_id' => null]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('handovers.items.search', ['search' => 'Laptop']))
            ->assertOk()
            ->assertJsonPath('itemTypes.0.id', $itemType->id)
            ->assertJsonPath('itemTypes.0.name', 'Laptop Search');
    }

    public function test_item_type_search_returns_no_results_for_short_search(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('handovers.items.search', ['search' => 'a']))
            ->assertOk()
            ->assertExactJson(['itemTypes' => []]);
    }

    public function test_receiver_search_returns_no_results_for_short_search(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('handovers.receivers.search', ['search' => 'a']))
            ->assertOk()
            ->assertExactJson(['users' => []]);
    }

    public function test_receiver_search_excludes_current_user_and_limits_results(): void
    {
        $sender = User::factory()->create(['name' => 'Pengirim Search']);
        User::factory()->count(25)->create(['name' => 'User Search']);

        $response = $this->actingAs($sender)
            ->getJson(route('handovers.receivers.search', ['search' => 'User Search']))
            ->assertOk();

        $this->assertCount(20, $response->json('users'));
        $this->assertNotContains($sender->id, collect($response->json('users'))->pluck('id')->all());
    }

    public function test_show_route_is_registered(): void
    {
        $this->assertSame('/handovers/42', route('handovers.show', ['handover' => 42], false));
    }
}
