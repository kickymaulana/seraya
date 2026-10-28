<?php

namespace Tests\Feature;

use App\Models\Handover;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoverConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sender_can_confirm_and_receiver_can_complete_handover(): void
    {
        $sender = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $receiver = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $itemType = ItemType::factory()->create(['name' => 'SEPATU SAFETY', 'unit' => 'PASANG']);

        $this->actingAs($sender)
            ->post(route('handovers.store'), [
                'type' => 'PENYERAHAN',
                'receiver_id' => $receiver->id,
                'transaction_date' => '2026-10-27',
                'items' => [[
                    'item_type_id' => $itemType->id,
                    'quantity' => 1,
                    'condition' => 'BAIK',
                ]],
            ])
            ->assertRedirect(route('handovers.index'));

        $handover = Handover::query()->latest('id')->firstOrFail();
        $this->assertSame('MENUNGGU_KONFIRMASI', $handover->status);

        $this->actingAs($sender)
            ->post(route('handovers.confirm-sender', $handover))
            ->assertRedirect(route('handovers.index'));

        $this->assertDatabaseHas('handovers', [
            'id' => $handover->id,
            'status' => 'MENUNGGU_KONFIRMASI_PENERIMA',
        ]);

        $this->actingAs($receiver)
            ->get(route('handovers.pending'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Handovers/Pending')->has('handovers.data', 1));

        $this->actingAs($receiver)
            ->post(route('handovers.confirm-receiver', $handover))
            ->assertRedirect(route('handovers.pending'));

        $this->assertDatabaseHas('handovers', [
            'id' => $handover->id,
            'status' => 'SELESAI',
        ]);
    }

    public function test_sender_cannot_create_handover_to_themselves(): void
    {
        $sender = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $itemType = ItemType::factory()->create();

        $this->actingAs($sender)
            ->from(route('handovers.create'))
            ->post(route('handovers.store'), [
                'type' => 'PENYERAHAN',
                'receiver_id' => $sender->id,
                'transaction_date' => '2026-10-27',
                'items' => [[
                    'item_type_id' => $itemType->id,
                    'quantity' => 1,
                    'condition' => 'BAIK',
                ]],
            ])
            ->assertSessionHasErrors([
                'receiver_id' => 'Pihak menerima tidak boleh sama dengan pihak yang menyerahkan.',
            ]);

        $this->assertDatabaseCount('handovers', 0);
    }

    public function test_handover_index_exposes_current_user_and_sender_handover_data(): void
    {
        $sender = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $receiver = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $handover = Handover::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'MENUNGU_KONFIRMASI',
        ]);

        $this->actingAs($sender)
            ->get(route('handovers.index'))
            ->assertInertia(fn ($page) => $page
                ->component('Handovers/Index')
                ->where('auth.user.id', $sender->id)
                ->where('handovers.data.0.id', $handover->id)
                ->where('handovers.data.0.sender.id', $sender->id));
    }

    public function test_only_the_sender_can_confirm_sender_step(): void
    {
        $sender = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $receiver = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $handover = Handover::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'MENUNGGU_KONFIRMASI',
        ]);

        $this->actingAs($receiver)
            ->post(route('handovers.confirm-sender', $handover))
            ->assertForbidden();
    }
}
