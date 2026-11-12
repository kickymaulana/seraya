<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveHandoverBatchRequest;
use App\Models\HandoverBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HandoverApprovalController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Handovers/Approvals', [
            'batches' => HandoverBatch::query()
                ->with(['sender:id,name', 'handovers.receiver:id,name', 'handovers.items.itemType:id,name,unit'])
                ->where('status', 'MENUNGGU_PERSETUJUAN_HSE')
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function update(ApproveHandoverBatchRequest $request, HandoverBatch $handoverBatch): RedirectResponse
    {
        abort_unless($handoverBatch->status === 'MENUNGGU_PERSETUJUAN_HSE', 422, 'Batch ini sudah diproses HSE.');

        DB::transaction(function () use ($handoverBatch, $request): void {
            $rejectedItems = collect($request->validated('rejected_items', []))->keyBy('id');
            $handovers = $handoverBatch->handovers()->with('items')->get();

            foreach ($handovers as $handover) {
                $approvedItemCount = 0;

                foreach ($handover->items as $item) {
                    $rejection = $rejectedItems->get($item->id);
                    $isRejected = $rejection !== null;

                    $item->update([
                        'approval_status' => $isRejected ? 'DITOLAK' : 'DISETUJUI',
                        'rejection_reason' => $isRejected ? $rejection['reason'] : null,
                    ]);

                    if (! $isRejected) {
                        $approvedItemCount++;
                    }
                }

                $handover->update([
                    'status' => $approvedItemCount > 0 ? 'MENUNGGU_KONFIRMASI_PENERIMA' : 'DITOLAK',
                ]);
            }

            $handoverBatch->update([
                'status' => $rejectedItems->isNotEmpty() ? 'SEBAGIAN_DISETUJUI_HSE' : 'DISETUJUI_HSE',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);
        });

        return to_route('handovers.approvals.index')->with('success', 'Persetujuan batch APD berhasil disimpan.');
    }
}
