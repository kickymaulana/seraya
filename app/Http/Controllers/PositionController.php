<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Positions/Index', ['positions' => Position::query()->orderBy('name')->get(['id', 'name', 'description', 'is_active'])]);
    }

    public function create(): Response
    {
        return Inertia::render('Positions/Create');
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        Position::query()->create($request->validated());

        return to_route('positions.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Position $position): Response
    {
        return Inertia::render('Positions/Edit', ['position' => $position->only(['id', 'name', 'description', 'is_active'])]);
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return to_route('positions.index')->with('success', 'Jabatan berhasil diperbarui.');
    }
}
