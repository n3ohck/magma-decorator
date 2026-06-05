<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EnvironmentBuilderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Builder/Environments', [
            'items' => Environment::query()
                ->withCount('zones')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, ImageOptimizer $optimizer)
    {
        $data = $this->validatedData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        foreach ($this->imageFields() as $field => [$folder, $profile]) {
            if ($request->hasFile($field)) {
                $data[$field] = $optimizer->store($request->file($field), $folder, $profile);
            }
        }

        Environment::create($data);

        return back()->with('success', 'Ambiente creado correctamente.');
    }

    public function update(Request $request, Environment $environment, ImageOptimizer $optimizer)
    {
        $data = $this->validatedData($request, $environment->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        foreach ($this->imageFields() as $field => [$folder, $profile]) {
            $removeField = 'remove_' . $field;

            if ($request->boolean($removeField)) {
                $this->deleteFile($environment->{$field});
                $data[$field] = null;
            }

            if ($request->hasFile($field)) {
                $this->deleteFile($environment->{$field});
                $data[$field] = $optimizer->store($request->file($field), $folder, $profile);
            }
        }

        $environment->update($data);

        return back()->with('success', 'Ambiente actualizado correctamente.');
    }

    public function destroy(Environment $environment)
    {
        foreach (array_keys($this->imageFields()) as $field) {
            $this->deleteFile($environment->{$field});
        }

        $environment->delete();

        return back()->with('success', 'Ambiente eliminado correctamente.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:environments,slug,' . $id],
            'type' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_image' => [$id ? 'nullable' : 'required', 'image', 'max:51200'],
            'preview_image' => ['nullable', 'image', 'max:51200'],
            'shadow_overlay_image' => ['nullable', 'image', 'max:51200'],
            'light_overlay_image' => ['nullable', 'image', 'max:51200'],
            'canvas_width' => ['nullable', 'integer', 'min:100'],
            'canvas_height' => ['nullable', 'integer', 'min:100'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'foreground_overlay_image' => ['nullable', 'image', 'max:51200'],
        ]);
    }

    private function imageFields(): array
    {
        // [folder, optimizer_profile]
        return [
            'base_image'              => ['environments/base',              'base'],
            'preview_image'           => ['environments/previews',          'preview'],
            'shadow_overlay_image'    => ['environments/overlays/shadows',  'overlay'],
            'light_overlay_image'     => ['environments/overlays/lights',   'overlay'],
            'foreground_overlay_image'=> ['environments/overlays/foregrounds', 'overlay'],
        ];
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
