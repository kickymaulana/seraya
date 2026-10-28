<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Department;
use App\Models\Factory;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = auth()->user()->load(['position', 'departments', 'factories']);

        return Inertia::render('Profile/Edit', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'position_id' => $user->position_id,
                'department_ids' => $user->departments->pluck('id')->values(),
                'factory_ids' => $user->factories->pluck('id')->values(),
            ],
            'positions' => Position::query()->where('is_active', true)->orderBy('level')->orderBy('name')->get(['id', 'name', 'level']),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'factories' => Factory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->update(['position_id' => $validated['position_id']]);
        $user->departments()->sync($validated['department_ids']);
        $user->factories()->sync($validated['factory_ids']);

        return to_route('profile.edit')->with('success', 'Profil organisasi berhasil diperbarui.');
    }
}
