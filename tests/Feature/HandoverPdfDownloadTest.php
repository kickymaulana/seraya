<?php

namespace Tests\Feature;

use App\Models\Handover;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoverPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_related_user_can_preview_current_handover_pdf(): void
    {
        $sender = User::factory()->create(['name' => 'Pengirim Aktual']);
        $receiver = User::factory()->create(['name' => 'Penerima Aktual']);
        $itemType = ItemType::factory()->create(['name' => 'Laptop Aktual', 'unit' => 'UNIT']);
        $handover = Handover::factory()->create([
            'number' => 'SRY-PDF-001',
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'transaction_date' => '2026-10-27',
            'purpose' => 'Pengujian PDF aktual',
        ]);
        $handover->items()->create([
            'item_type_id' => $itemType->id,
            'quantity' => 2,
            'condition' => 'BAIK',
            'notes' => 'Data item aktual',
        ]);

        $response = $this->actingAs($sender)->get(route('handovers.pdf', $handover));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=SRY-PDF-001.pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('SRY-PDF-001.pdf', $response->headers->get('content-disposition'));
        $this->assertDatabaseHas('handover_items', [
            'handover_id' => $handover->id,
            'item_type_id' => $itemType->id,
            'quantity' => 2,
            'notes' => 'Data item aktual',
        ]);
    }

    public function test_unauthenticated_user_cannot_download_handover_pdf(): void
    {
        $handover = Handover::factory()->create();

        $this->get(route('handovers.pdf', $handover))
            ->assertForbidden();
    }

    public function test_unrelated_authenticated_user_cannot_download_handover_pdf(): void
    {
        $handover = Handover::factory()->create();
        $unrelatedUser = User::factory()->create();

        $this->actingAs($unrelatedUser)
            ->get(route('handovers.pdf', $handover))
            ->assertForbidden();
    }
}
