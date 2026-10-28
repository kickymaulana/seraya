<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Factory as FactoryModel;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchHandoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_open_batch_page_with_filters_and_active_users(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $activeUser = User::factory()->create(['active' => true]);
        $inactiveUser = User::factory()->create(['active' => false]);
        $department = Department::factory()->create();
        $factory = FactoryModel::query()->create(['name' => 'Pabrik Uji', 'is_active' => true]);
        $activeUser->departments()->attach($department);
        $activeUser->factories()->attach($factory);
        ItemType::factory()->create(['name' => 'HELM APD']);

        $this->actingAs($viewer)
            ->get(route('handovers.batch.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Handovers/BatchCreate')
                ->has('departments', 1)
                ->has('factories', 1)
                ->where('users', fn ($users) => collect($users)->pluck('id')->contains($activeUser->id) && ! collect($users)->pluck('id')->contains($inactiveUser->id)));
    }

    public function test_viewer_can_create_one_handover_and_items_for_each_receiver(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer', 'active' => true]);
        $receivers = User::factory()->count(2)->create(['active' => true]);
        $itemType = ItemType::factory()->create(['name' => 'HELM APD']);

        $this->actingAs($viewer)
            ->post(route('handovers.batch.store'), [
                'type' => 'PENYERAHAN',
                'receiver_ids' => $receivers->pluck('id')->all(),
                'transaction_date' => '2026-10-27',
                'purpose' => 'Distribusi APD awal',
                'notes' => 'Paket sama',
                'items' => [[
                    'item_type_id' => $itemType->id,
                    'quantity' => 2,
                    'condition' => 'BAIK',
                ]],
            ])
            ->assertRedirect(route('handovers.index'))
            ->assertSessionHas('success', '2 transaksi batch berhasil dibuat.');

        $this->assertDatabaseCount('handovers', 2);
        $this->assertDatabaseCount('handover_items', 2);
        $this->assertDatabaseHas('handovers', ['sender_id' => $viewer->id, 'receiver_id' => $receivers[0]->id, 'status' => 'MENUNGGU_KONFIRMASI']);
        $this->assertDatabaseHas('handovers', ['sender_id' => $viewer->id, 'receiver_id' => $receivers[1]->id, 'status' => 'MENUNGGU_KONFIRMASI']);
        $this->assertDatabaseHas('handover_items', ['item_type_id' => $itemType->id, 'quantity' => 2, 'condition' => 'BAIK']);
    }

    public function test_batch_store_accepts_manual_item(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'active' => true]);
        $receiver = User::factory()->create(['active' => true]);

        $this->actingAs($operator)
            ->post(route('handovers.batch.store'), [
                'type' => 'PENYERAHAN',
                'receiver_ids' => [$receiver->id],
                'transaction_date' => '2026-10-27',
                'items' => [[
                    'manual_name' => 'Kursi Lipat',
                    'manual_unit' => 'UNIT',
                    'quantity' => 2,
                    'condition' => 'BAIK',
                ]],
            ])
            ->assertRedirect(route('handovers.index'));

        $this->assertDatabaseHas('handover_items', [
            'manual_name' => 'Kursi Lipat',
            'manual_unit' => 'UNIT',
            'item_type_id' => null,
            'quantity' => 2,
        ]);
    }

    public function test_batch_store_rejects_empty_receivers_and_items(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'active' => true]);

        $this->actingAs($operator)
            ->from(route('handovers.batch.create'))
            ->post(route('handovers.batch.store'), [
                'type' => 'PENYERAHAN',
                'receiver_ids' => [],
                'transaction_date' => '2026-10-27',
                'items' => [],
            ])
            ->assertSessionHasErrors([
                'receiver_ids' => 'Pilih minimal satu penerima.',
                'items' => 'Tambahkan minimal satu item.',
            ]);

        $this->assertDatabaseCount('handovers', 0);
    }

    public function test_batch_store_rejects_sender_as_recipient_and_remains_atomic(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'active' => true]);
        $receiver = User::factory()->create(['active' => true]);
        $itemType = ItemType::factory()->create();

        $this->actingAs($operator)
            ->from(route('handovers.batch.create'))
            ->post(route('handovers.batch.store'), [
                'type' => 'PENYERAHAN',
                'receiver_ids' => [$receiver->id, $operator->id],
                'transaction_date' => '2026-10-27',
                'items' => [[
                    'item_type_id' => $itemType->id,
                    'quantity' => 1,
                    'condition' => 'BAIK',
                ]],
            ])
            ->assertSessionHasErrors('receiver_ids.1');

        $this->assertDatabaseCount('handovers', 0);
        $this->assertDatabaseCount('handover_items', 0);
    }

    public function test_guest_cannot_access_batch_endpoints(): void
    {
        auth()->logout();

        $this->get(route('handovers.batch.create'))->assertRedirect(route('login'));
        $this->post(route('handovers.batch.store'), [])->assertRedirect(route('login'));
        $this->assertDatabaseCount('handovers', 0);
    }
}
