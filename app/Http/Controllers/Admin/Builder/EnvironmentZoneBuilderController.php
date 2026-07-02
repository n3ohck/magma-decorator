<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Models\EnvironmentZone;
use App\Models\EnvironmentZoneGroup;
use App\Services\ImageOptimizer;
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
                ->with(['environment', 'zoneGroup'])
                ->orderBy('environment_id')
                ->orderBy('sort_order')
                ->get(),
            'environments' => Environment::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'zoneGroups' => EnvironmentZoneGroup::query()
                ->where('is_active', true)
                ->orderBy('environment_id')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function store(Request $request, ImageOptimizer $optimizer)
    {
        $data = $this->validatedData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('mask_image')) {
            // Perfil 'mask': PNG lossless — preserva alpha binario exacto
            $data['mask_image'] = $optimizer->store(
                $request->file('mask_image'), 'environments/masks', 'mask'
            );
        } elseif ($request->filled('sam_mask_path')) {
            $data['mask_image'] = $request->string('sam_mask_path')->toString();
        }

        EnvironmentZone::create($data);

        return back()->with('success', 'Zona creada correctamente.');
    }

    public function update(Request $request, EnvironmentZone $environmentZone, ImageOptimizer $optimizer)
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
            $data['mask_image'] = $optimizer->store(
                $request->file('mask_image'), 'environments/masks', 'mask'
            );
        } elseif ($request->filled('sam_mask_path')) {
            $this->deleteFile($environmentZone->mask_image);
            $data['mask_image'] = $request->string('sam_mask_path')->toString();
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
            'zone_group_id'  => ['nullable', 'exists:environment_zone_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'zone_type' => ['nullable', 'string', 'max:255'],
            // Requerido al crear solo si no se proporciona una máscara vía sam_mask_path
            'mask_image' => [
                $id
                    ? 'nullable'
                    : \Illuminate\Validation\Rule::requiredIf(fn () => ! $request->filled('sam_mask_path')),
                'nullable',
                'image',
                'max:51200',
            ],
            'default_texture_scale' => ['nullable', 'numeric', 'min:0.1'],
            'default_texture_rotation' => ['nullable', 'numeric'],
            'default_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'supports_perspective' => ['nullable', 'boolean'],
            'default_book_match' => ['nullable', 'boolean'],
            'polygon_points' => ['nullable', 'string'],
            'perspective_points' => ['nullable', 'string'],
            'sam_mask_path'      => ['nullable', 'string', 'max:500'],
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
