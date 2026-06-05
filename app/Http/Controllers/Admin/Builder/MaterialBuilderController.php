<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MaterialBuilderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Builder/Materials', [
            'items' => Material::query()
                ->with('category')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'categories' => MaterialCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, ImageOptimizer $optimizer)
    {
        $data = $this->validatedData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('texture_image')) {
            $data['texture_image'] = $optimizer->store(
                $request->file('texture_image'), 'materials/textures', 'texture'
            );
        }

        if ($request->hasFile('thumbnail_image')) {
            $data['thumbnail_image'] = $optimizer->store(
                $request->file('thumbnail_image'), 'materials/thumbnails', 'thumbnail'
            );
        }

        Material::create($data);

        return back()->with('success', 'Material creado correctamente.');
    }

    public function update(Request $request, Material $material, ImageOptimizer $optimizer)
    {
        $data = $this->validatedData($request, $material->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->boolean('remove_texture_image')) {
            $this->deleteFile($material->texture_image);
            $data['texture_image'] = null;
        } elseif ($request->hasFile('texture_image')) {
            $this->deleteFile($material->texture_image);
            $data['texture_image'] = $optimizer->store(
                $request->file('texture_image'), 'materials/textures', 'texture'
            );
        } else {
            // No change — remove from update array to keep the existing value
            unset($data['texture_image']);
        }

        if ($request->boolean('remove_thumbnail_image')) {
            $this->deleteFile($material->thumbnail_image);
            $data['thumbnail_image'] = null;
        } elseif ($request->hasFile('thumbnail_image')) {
            $this->deleteFile($material->thumbnail_image);
            $data['thumbnail_image'] = $optimizer->store(
                $request->file('thumbnail_image'), 'materials/thumbnails', 'thumbnail'
            );
        } else {
            unset($data['thumbnail_image']);
        }

        $material->update($data);

        return back()->with('success', 'Material actualizado correctamente.');
    }

    public function destroy(Material $material)
    {
        $this->deleteFile($material->texture_image);
        $this->deleteFile($material->thumbnail_image);

        $material->delete();

        return back()->with('success', 'Material eliminado correctamente.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'material_category_id' => ['required', 'exists:material_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:materials,slug,' . $id],
            'sku' => ['nullable', 'string', 'max:255'],
            'origin_country' => ['nullable', 'string', 'max:255'],
            'finish' => ['nullable', 'string', 'max:255'],
            'base_color' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'texture_image' => [$id ? 'nullable' : 'required', 'image', 'max:51200'],
            'thumbnail_image' => ['nullable', 'image', 'max:51200'],
            'default_scale' => ['nullable', 'numeric', 'min:0.1'],
            'default_rotation' => ['nullable', 'numeric'],
            'default_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'is_featured' => ['nullable', 'boolean'],
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
