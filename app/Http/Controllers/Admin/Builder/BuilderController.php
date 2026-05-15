<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Models\EnvironmentZone;
use App\Models\Lead;
use App\Models\Material;
use App\Models\MaterialCategory;
use Inertia\Inertia;

class BuilderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Builder/Dashboard', [
            'stats' => [
                'material_categories' => MaterialCategory::count(),
                'materials' => Material::count(),
                'active_materials' => Material::where('is_active', true)->count(),
                'environments' => Environment::count(),
                'active_environments' => Environment::where('is_active', true)->count(),
                'environment_zones' => EnvironmentZone::count(),
                'new_leads' => Lead::where('status', 'new')->count(),
                'total_leads' => Lead::count(),
            ],
            'latestLeads' => Lead::query()
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
