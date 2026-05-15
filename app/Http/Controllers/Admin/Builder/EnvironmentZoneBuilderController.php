<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Models\EnvironmentZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EnvironmentZoneBuilderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Builder/EnvironmentZones', [
            'items' => EnvironmentZone::query()
                ->with('environment')
                ->orderBy('environment_id')
                ->orderBy('sort_order')
                ->get(),
            'environments' => Environment::query()
                ->where('is_active', true)
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

        if ($request->hasFile('mask_image')) {
            $data['mask_image'] = $request->file('mask_image')
                ->store('environments/masks', 'public');
        }

        EnvironmentZone::create($data);

        return back()->with('success', 'Zona creada correctamente.');
    }

    public function update(Request $request, EnvironmentZone $environmentZone)
    {
        $data = $this->validatedData($request, $environmentZone->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->boolean('remove_mask_image')) {
            $this->deleteFile($environmentZone->mask_image);
            $data['mask_image'] = null;
        }

        if ($request->hasFile('mask_image')) {
            $this->deleteFile($environmentZone->mask_image);

            $data['mask_image'] = $request->file('mask_image')
                ->store('environments/masks', 'public');
        }

        $environmentZone->update($data);

        return back()->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy(EnvironmentZone $environmentZone)
    {
        $this->deleteFile($environmentZone->mask_image);

        $environmentZone->delete();

        return back()->with('success', 'Zona eliminada correctamente.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'environment_id' => ['required', 'exists:environments,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'zone_type' => ['nullable', 'string', 'max:255'],
            'mask_image' => [$id ? 'nullable' : 'required', 'image', 'max:20480'],
            'default_texture_scale' => ['nullable', 'numeric', 'min:0.1'],
            'default_texture_rotation' => ['nullable', 'numeric'],
            'default_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'supports_perspective' => ['nullable', 'boolean'],
            'polygon_points' => ['nullable', 'string'],
            'perspective_points' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
