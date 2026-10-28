<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Handover;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
            'type' => $request->string('type')->toString(),
            'status' => $request->string('status')->toString(),
            'sender_id' => $request->integer('sender_id') ?: null,
            'receiver_id' => $request->integer('receiver_id') ?: null,
            'item_type_ids' => collect($request->input('item_type_ids', []))->map(fn (mixed $id): int => (int) $id)->filter()->values()->all(),
        ];

        $handovers = Handover::query()
            ->with([
                'sender:id,name',
                'receiver:id,name',
                'items.itemType:id,name,category_id,unit',
                'items.itemType.category:id,name',
            ])
            ->when($filters['date_from'], fn (Builder $query, string $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'], fn (Builder $query, string $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['type'], fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['sender_id'], fn (Builder $query, int $senderId) => $query->where('sender_id', $senderId))
            ->when($filters['receiver_id'], fn (Builder $query, int $receiverId) => $query->where('receiver_id', $receiverId))
            ->when($filters['item_type_ids'], fn (Builder $query, array $itemTypeIds) => $query->whereHas('items', fn (Builder $items) => $items->whereIn('item_type_id', $itemTypeIds)))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Reports/Index', [
            'handovers' => $handovers,
            'filters' => $filters,
            'itemTypes' => ItemType::query()->with('category:id,name')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'category_id', 'unit']),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
            'types' => ['PENYERAHAN', 'PENGEMBALIAN', 'PEMINJAMAN', 'PENGGANTIAN', 'MUTASI'],
            'statuses' => ['MENUNGGU_KONFIRMASI', 'MENUNGGU_KONFIRMASI_PENERIMA', 'SELESAI'],
        ]);
    }
}
