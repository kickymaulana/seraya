<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\ItemType;
use App\Models\Position;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('MasterData/Index', [
            'counts' => [
                'categories' => Category::query()->count(),
                'itemTypes' => ItemType::query()->count(),
                'departments' => Department::query()->count(),
                'positions' => Position::query()->count(),
                'users' => User::query()->count(),
            ],
        ]);
    }
}
