<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\Department;
use App\Models\Factory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->with(['position:id,name', 'departments:id,name', 'factories:id,name'])
                ->orderBy('name')
                ->get(['id', 'nik', 'name', 'email', 'avatar_url', 'active', 'tanggal_masuk', 'role', 'position_id']),
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load(['position', 'departments', 'factories']);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'nik' => $user->nik,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'active' => $user->active,
                'tanggal_masuk' => $user->tanggal_masuk?->format('Y-m-d'),
                'role' => $user->role,
                'position_id' => $user->position_id,
                'department_ids' => $user->departments->pluck('id')->values(),
                'factory_ids' => $user->factories->pluck('id')->values(),
            ],
            'positions' => Position::query()->orderBy('level')->orderBy('name')->get(['id', 'name', 'level']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'factories' => Factory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update([
            'role' => $validated['role'],
            'active' => $validated['active'],
            'position_id' => $validated['position_id'] ?? null,
        ]);
        $user->syncRoles([$validated['role']]);
        $user->departments()->sync($validated['department_ids']);
        $user->factories()->sync($validated['factory_ids']);

        return to_route('users.index')->with('success', 'User berhasil diperbarui.');
    }
}
