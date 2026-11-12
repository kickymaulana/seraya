<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Handover;
use App\Models\HandoverBatch;
use App\Models\HandoverItem;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoverBatchApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_apd_batch_waits_for_hse_and_moves_approved_items_to_receiver_confirmation(): void
    {
        $sender = User::factory()->create(['role' => 'operator', 'active' => true]);
        $receivers = User::factory()->count(2)->create(['active' => true]);
        $apdCategory = Category::factory()->create(['name' => 'APD']);
        $itemType = ItemType::factory()->create(['category_id' => $apdCategory->id]);

        $this->actingAs($sender)->post(route('handovers.batch.store'), [
            'type' => 'PENYERAHAN',
            'receiver_ids' => $receivers->pluck('id')->all(),
            'transaction_date' => '2026-11-12',
            'items' => [[
                'item_type_id' => $itemType->id,
                'quantity' => 1,
                'condition' => 'BAIK',
            ]],
        ])->assertRedirect(route('handovers.index'));

        $batch = HandoverBatch::query()->firstOrFail();
        $this->assertSame('MENUNGGU_PERSETUJUAN_HSE', $batch->status);
        $this->assertDatabaseCount('handovers', 2);
        $this->assertDatabaseHas('handovers', ['handover_batch_id' => $batch->id, 'status' => 'MENUNGGU_PERSETUJUAN_HSE']);

        $hse = User::factory()->create(['role' => 'hse']);
        $this->actingAs($hse)->put(route('handovers.approvals.update', $batch))->assertRedirect(route('handovers.approvals.index'));

        $this->assertDatabaseHas('handover_batches', ['id' => $batch->id, 'status' => 'DISETUJUI_HSE', 'approved_by' => $hse->id]);
        $this->assertDatabaseCount('handover_items', 2);
        $this->assertDatabaseHas('handover_items', ['approval_status' => 'DISETUJUI']);
        $this->assertDatabaseHas('handovers', ['status' => 'MENUNGGU_KONFIRMASI_PENERIMA']);
    }

    public function test_hse_can_reject_an_exception_while_approved_items_remain_receivable(): void
    {
        $hse = User::factory()->create(['role' => 'hse']);
        $batch = HandoverBatch::query()->create([
            'number' => 'BATCH-APD-TEST',
            'sender_id' => User::factory()->create()->id,
            'status' => 'MENUNGGU_PERSETUJUAN_HSE',
            'transaction_date' => '2026-11-12',
        ]);
        $handover = Handover::factory()->create(['handover_batch_id' => $batch->id, 'status' => 'MENUNGGU_PERSETUJUAN_HSE']);
        $approvedItem = HandoverItem::query()->create([
            'handover_id' => $handover->id,
            'item_type_id' => ItemType::factory()->create()->id,
            'quantity' => 1,
            'condition' => 'BAIK',
        ]);
        $rejectedItem = HandoverItem::query()->create([
            'handover_id' => $handover->id,
            'item_type_id' => ItemType::factory()->create()->id,
            'quantity' => 1,
            'condition' => 'BAIK',
        ]);

        $this->actingAs($hse)->put(route('handovers.approvals.update', $batch), [
            'rejected_items' => [['id' => $rejectedItem->id, 'reason' => 'Ukuran tidak sesuai.']],
        ])->assertRedirect(route('handovers.approvals.index'));

        $this->assertDatabaseHas('handover_items', ['id' => $approvedItem->id, 'approval_status' => 'DISETUJUI']);
        $this->assertDatabaseHas('handover_items', ['id' => $rejectedItem->id, 'approval_status' => 'DITOLAK', 'rejection_reason' => 'Ukuran tidak sesuai.']);
        $this->assertDatabaseHas('handovers', ['id' => $handover->id, 'status' => 'MENUNGGU_KONFIRMASI_PENERIMA']);
        $this->assertDatabaseHas('handover_batches', ['id' => $batch->id, 'status' => 'SEBAGIAN_DISETUJUI_HSE']);
    }

    public function test_non_hse_user_cannot_approve_an_apd_batch(): void
    {
        $batch = HandoverBatch::query()->create([
            'number' => 'BATCH-APD-UNAUTHORIZED',
            'sender_id' => User::factory()->create()->id,
            'status' => 'MENUNGGU_PERSETUJUAN_HSE',
            'transaction_date' => '2026-11-12',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'operator']))
            ->put(route('handovers.approvals.update', $batch))
            ->assertForbidden();
    }
}
