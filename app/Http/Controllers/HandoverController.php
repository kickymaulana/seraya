<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchHandoverRequest;
use App\Http\Requests\StoreHandoverRequest;
use App\Models\Department;
use App\Models\Factory;
use App\Models\Handover;
use App\Models\ItemType;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class HandoverController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Handovers/Index', [
            'handovers' => Handover::query()->with(['sender:id,name', 'receiver:id,name', 'items.itemType:id,name,unit'])->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Handover $handover): InertiaResponse
    {
        $user = request()->user();
        abort_unless(
            in_array($user->role, ['admin', 'operator'], true)
                || in_array($user->id, [$handover->sender_id, $handover->receiver_id], true),
            403,
        );

        $handover = Handover::query()
            ->with(['sender:id,name,nik', 'receiver:id,name,nik', 'items.itemType:id,name,unit'])
            ->findOrFail($handover->id);

        return Inertia::render('Handovers/Show', [
            'handover' => $handover,
            'timeline' => [
                ['label' => 'Transaksi dibuat', 'timestamp' => $handover->created_at?->toISOString(), 'completed' => true],
                ['label' => 'Pengirim mengonfirmasi', 'timestamp' => $handover->sender_confirmed_at?->toISOString(), 'completed' => $handover->sender_confirmed_at !== null],
                ['label' => 'Penerima mengonfirmasi', 'timestamp' => $handover->receiver_confirmed_at?->toISOString(), 'completed' => $handover->receiver_confirmed_at !== null],
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Handovers/Create');
    }

    public function searchItemTypes(Request $request): JsonResponse
    {
        $search = trim($request->string('search')->toString());

        if (mb_strlen($search) < 2) {
            return response()->json(['itemTypes' => []]);
        }

        $itemTypes = ItemType::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'category_id', 'unit']);

        return response()->json(['itemTypes' => $itemTypes]);
    }

    public function searchReceivers(Request $request): JsonResponse
    {
        $search = trim($request->string('search')->toString());

        if (mb_strlen($search) < 2) {
            return response()->json(['users' => []]);
        }

        $users = User::query()
            ->with(['departments:id,name'])
            ->where('active', true)
            ->where('id', '!=', request()->user()->id)
            ->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('departments', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'nik']);

        return response()->json(['users' => $users]);
    }

    public function store(StoreHandoverRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $handover = DB::transaction(function () use ($data, $request): Handover {
            $handover = Handover::query()->create([
                'number' => 'SRY-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'type' => $data['type'],
                'sender_id' => $request->user()->id,
                'receiver_id' => $data['receiver_id'],
                'transaction_date' => $data['transaction_date'],
                'purpose' => $data['purpose'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'MENUNGGU_KONFIRMASI',
            ]);
            $handover->items()->createMany($data['items']);

            return $handover;
        });

        return to_route('handovers.index')->with('success', "Transaksi {$handover->number} berhasil dibuat.");
    }

    public function batchCreate(): InertiaResponse
    {
        return Inertia::render('Handovers/BatchCreate', [
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'factories' => Factory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->with(['departments:id,name', 'factories:id,name'])->where('active', true)->where('id', '!=', request()->user()->id)->orderBy('name')->get(['id', 'name', 'nik']),
        ]);
    }

    public function batchStore(StoreBatchHandoverRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $createdCount = DB::transaction(function () use ($data, $request): int {
            foreach ($data['receiver_ids'] as $receiverId) {
                $handover = Handover::query()->create([
                    'number' => 'SRY-'.now()->format('YmdHisv').'-'.random_int(100, 999),
                    'type' => $data['type'],
                    'sender_id' => $request->user()->id,
                    'receiver_id' => $receiverId,
                    'transaction_date' => $data['transaction_date'],
                    'purpose' => $data['purpose'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'status' => 'MENUNGGU_KONFIRMASI',
                ]);
                $handover->items()->createMany($data['items']);
            }

            return count($data['receiver_ids']);
        });

        return to_route('handovers.index')->with('success', "{$createdCount} transaksi batch berhasil dibuat.");
    }

    public function downloadPdf(Handover $handover): Response
    {
        abort_unless(
            in_array(request()->user()->id, [$handover->sender_id, $handover->receiver_id], true),
            403,
        );

        $handover = Handover::query()
            ->with([
                'sender:id,name,nik,position_id',
                'sender.position:id,name',
                'sender.departments:id,name',
                'receiver:id,name,nik,position_id',
                'receiver.position:id,name',
                'receiver.departments:id,name',
                'items.itemType:id,name,unit',
            ])
            ->findOrFail($handover->id);

        return Pdf::loadView('handovers.pdf', compact('handover'))
            ->stream("{$handover->number}.pdf");
    }

    public function pending(): InertiaResponse
    {
        return Inertia::render('Handovers/Pending', [
            'handovers' => Handover::query()
                ->with(['sender:id,name', 'receiver:id,name', 'items.itemType:id,name,unit'])
                ->where('receiver_id', request()->user()->id)
                ->whereIn('status', ['MENUNGGU_KONFIRMASI_PENERIMA'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function confirmSender(Handover $handover): RedirectResponse
    {
        abort_unless($handover->sender_id === request()->user()->id, 403);
        abort_unless($handover->status === 'MENUNGGU_KONFIRMASI', 422, 'Transaksi tidak menunggu konfirmasi pihak menyerahkan.');

        $handover->update([
            'sender_confirmed_at' => now(),
            'status' => 'MENUNGGU_KONFIRMASI_PENERIMA',
        ]);

        return to_route('handovers.index')->with('success', 'Serah terima Anda telah dikonfirmasi.');
    }

    public function confirmReceiver(Handover $handover): RedirectResponse
    {
        abort_unless($handover->receiver_id === request()->user()->id, 403);
        abort_unless($handover->status === 'MENUNGGU_KONFIRMASI_PENERIMA', 422, 'Transaksi belum dapat dikonfirmasi penerima.');

        $handover->update([
            'receiver_confirmed_at' => now(),
            'status' => 'SELESAI',
        ]);

        return to_route('handovers.pending')->with('success', 'Penerimaan barang telah dikonfirmasi.');
    }
}
