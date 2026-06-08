<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Models\EnvironmentZone;
use App\Models\EnvironmentZoneGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EnvironmentZoneGroupBuilderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Builder/EnvironmentZoneGroups', [
            'items' => EnvironmentZoneGroup::query()
                ->with(['environment', 'zones'])
                ->orderBy('environment_id')
                ->orderBy('sort_order')
                ->get(),
            'environments' => Environment::query()
                ->where('is_active', true)
                ->with(['zones' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $group = EnvironmentZoneGroup::create($data);

        if ($request->filled('zone_ids')) {
            EnvironmentZone::whereIn('id', $request->input('zone_ids'))
                ->where('environment_id', $group->environment_id)
                ->update(['zone_group_id' => $group->id]);
        }

        return back()->with('success', 'Grupo creado correctamente.');
    }

    public function update(Request $request, EnvironmentZoneGroup $environmentZoneGroup)
    {
        $data = $this->validatedData($request, $environmentZoneGroup->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $environmentZoneGroup->update($data);

        // Sync zones: detach all from this group, then attach selected ones
        EnvironmentZone::where('zone_group_id', $environmentZoneGroup->id)
            ->update(['zone_group_id' => null]);

        if ($request->filled('zone_ids')) {
            EnvironmentZone::whereIn('id', $request->input('zone_ids'))
                ->where('environment_id', $environmentZoneGroup->environment_id)
                ->update(['zone_group_id' => $environmentZoneGroup->id]);
        }

        return back()->with('success', 'Grupo actualizado correctamente.');
    }

    public function destroy(EnvironmentZoneGroup $environmentZoneGroup)
    {
        // Detach zones before deleting (nullOnDelete handles it via FK but explicit is safer)
        EnvironmentZone::where('zone_group_id', $environmentZoneGroup->id)
            ->update(['zone_group_id' => null]);

        $environmentZoneGroup->delete();

        return back()->with('success', 'Grupo eliminado correctamente.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'environment_id' => ['required', 'exists:environments,id'],
            'name'           => ['required', 'string', 'max:255'],
            'slug'           => ['nullable', 'string', 'max:255'],
            'color'          => ['nullable', 'string', 'max:20'],
            'icon'           => ['nullable', 'string', 'max:100'],
            'label_x'        => ['nullable', 'numeric', 'min:0', 'max:1'],
            'label_y'        => ['nullable', 'numeric', 'min:0', 'max:1'],
            'is_active'      => ['nullable', 'boolean'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'zone_ids'       => ['nullable', 'array'],
            'zone_ids.*'     => ['integer', 'exists:environment_zones,id'],
        ]);
    }
}
