<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\MaterialCategory;
use Inertia\Inertia;

class DecoratorController extends Controller
{
    public function index()
    {
        return Inertia::render('Decorator/Index', [
            'environments' => Environment::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Environment $environment)
    {
        $environment->load([
            'zones' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order'),
            'activeZoneGroups.activeZones',
        ]);

        return Inertia::render('Decorator/Show', [
            'environment' => $environment,
            'categories' => MaterialCategory::query()
                ->where('is_active', true)
                ->with([
                    'materials' => fn ($query) => $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name'),
                ])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
