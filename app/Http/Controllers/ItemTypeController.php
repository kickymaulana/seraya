<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemTypeRequest;
use App\Http\Requests\UpdateItemTypeRequest;
use App\Models\Category;
use App\Models\ItemType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ItemTypes/Index', [
            'itemTypes' => ItemType::query()->with('category:id,name')->orderBy('name')->get(['id', 'name', 'category_id', 'unit', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ItemTypes/Create', ['categories' => $this->categories()]);
    }

    public function store(StoreItemTypeRequest $request): RedirectResponse
    {
        ItemType::query()->create($request->validated());

        return to_route('item-types.index')->with('success', 'Jenis barang berhasil ditambahkan.');
    }

    public function edit(ItemType $itemType): Response
    {
        return Inertia::render('ItemTypes/Edit', [
            'itemType' => $itemType->only(['id', 'name', 'category_id', 'unit', 'is_active']),
            'categories' => $this->categories(),
        ]);
    }

    public function update(UpdateItemTypeRequest $request, ItemType $itemType): RedirectResponse
    {
        $itemType->update($request->validated());

        return to_route('item-types.index')->with('success', 'Jenis barang berhasil diperbarui.');
    }

    private function categories(): array
    {
        return Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
    }
}
