<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Models\EnvironmentZone;
use App\Models\Lead;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
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
            'settings' => [
                'ai_render_enabled' => Setting::getBool('ai_render_enabled', true),
            ],
        ]);
    }

    /**
     * Actualiza las preferencias globales del decorador (toggles del builder).
     */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'ai_render_enabled' => ['required', 'boolean'],
        ]);

        Setting::set('ai_render_enabled', $data['ai_render_enabled'] ? '1' : '0');

        return back()->with('success', 'Preferencias actualizadas correctamente.');
    }
}
