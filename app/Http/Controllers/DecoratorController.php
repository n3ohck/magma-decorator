<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\MaterialCategory;
use App\Models\Setting;
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

        // Materiales por categoría. Si el ambiente no usa "todos los materiales",
        // se limita a los seleccionados en su configuración (tabla environment_material).
        $allowedIds = $environment->all_materials
            ? null
            : $environment->materials()->pluck('materials.id');

        $categories = MaterialCategory::query()
            ->where('is_active', true)
            ->with([
                'materials' => function ($query) use ($allowedIds) {
                    $query->where('is_active', true);

                    if ($allowedIds !== null) {
                        $query->whereIn('id', $allowedIds);
                    }

                    $query->orderBy('sort_order')->orderBy('name');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Oculta categorías que quedaron sin materiales tras el filtro.
        if ($allowedIds !== null) {
            $categories = $categories->filter(fn ($category) => $category->materials->isNotEmpty())->values();
        }

        return Inertia::render('Decorator/Show', [
            'environment' => $environment,
            'categories' => $categories,
            'aiRenderEnabled' => Setting::getBool('ai_render_enabled', true),
        ]);
    }
}
