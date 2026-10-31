<?php

namespace Tests\Feature;

use App\Models\Handover;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_reports(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/Index')
                ->has('filters')
                ->has('itemTypes')
                ->has('handovers'));
    }

    public function test_report_can_filter_by_date_and_item_type(): void
    {
        $viewer = User::factory()->create();
        $sender = User::factory()->create(['tanggal_masuk' => '2026-02-02']);
        $receiver = User::factory()->create(['tanggal_masuk' => '2026-03-03']);
        $itemType = ItemType::factory()->create(['name' => 'Laptop Laporan']);
        $included = Handover::factory()->create([
            'transaction_date' => '2026-10-28',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
        ]);
        $included->items()->create(['item_type_id' => $itemType->id, 'quantity' => 1, 'condition' => 'BAIK']);
        $excluded = Handover::factory()->create(['transaction_date' => '2026-10-01']);
        $excluded->items()->create(['item_type_id' => ItemType::factory()->create()->id, 'quantity' => 1, 'condition' => 'BAIK']);

        $this->actingAs($viewer)
            ->get(route('reports.index', [
                'date_from' => '2026-10-20',
                'item_type_ids' => [$itemType->id],
            ]))
            ->assertInertia(fn ($page) => $page
                ->where('handovers.data.0.id', $included->id)
                ->where('handovers.data', fn ($data) => collect($data)->pluck('id')->doesntContain($excluded->id))
                ->where('handovers.data.0.sender.tanggal_masuk', '2026-02-02T00:00:00.000000Z')
                ->where('handovers.data.0.receiver.tanggal_masuk', '2026-03-03T00:00:00.000000Z'));
    }
}
