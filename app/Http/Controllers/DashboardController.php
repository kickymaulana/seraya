<?php

namespace App\Http\Controllers;

use App\Models\Handover;
use App\Models\ItemType;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $categoryBreakdown = ItemType::query()
            ->select('category_id', DB::raw('count(*) as total'))
            ->with('category:id,name')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn (ItemType $itemType): array => ['id' => $itemType->category_id, 'name' => $itemType->category?->name ?? 'Tanpa kategori', 'total' => (int) $itemType->total])
            ->values();

        return Inertia::render('Dashboard', [
            'user' => request()->user()->only(['name', 'role']),
            'summary' => [
                'total' => Handover::query()->count(),
                'itemTypes' => ItemType::query()->where('is_active', true)->count(),
                'completed' => Handover::query()->where('status', 'SELESAI')->count(),
                'pending' => Handover::query()->whereIn('status', ['MENUNGGU_KONFIRMASI', 'MENUNGGU_KONFIRMASI_PENERIMA'])->count(),
            ],
            'categoryBreakdown' => $categoryBreakdown,
        ]);
    }
}
