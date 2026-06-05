<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MaterialCategoryBuilderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Builder/MaterialCategories', [
            'items' => MaterialCategory::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, ImageOptimizer $optimizer)
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $optimizer->store(
                $request->file('cover_image'), 'material-categories', 'preview'
            );
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        MaterialCategory::create($data);

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, MaterialCategory $materialCategory, ImageOptimizer $optimizer)
    {
        $data = $this->validatedData($request, $materialCategory->id);

        if ($request->boolean('remove_cover_image')) {
            $this->deleteFile($materialCategory->cover_image);
            $data['cover_image'] = null;
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteFile($materialCategory->cover_image);
            $data['cover_image'] = $optimizer->store(
                $request->file('cover_image'), 'material-categories', 'preview'
            );
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $materialCategory->update($data);

        return back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(MaterialCategory $materialCategory)
    {
        $this->deleteFile($materialCategory->cover_image);

        $materialCategory->delete();

        return back()->with('success', 'Categoría eliminada correctamente.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:material_categories,slug,' . $id],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:51200'],
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
