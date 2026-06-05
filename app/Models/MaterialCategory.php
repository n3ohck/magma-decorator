<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class MaterialCategory extends Model
{
    use CrudTrait;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'keywords',
        'cover_image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'keywords'   => 'array',
    ];

    protected $appends = [
        'cover_image_url',
    ];

    public function materials()
    {
        return $this->hasMany(Material::class, 'material_category_id');
    }

    public function activeMaterials()
    {
        return $this->hasMany(Material::class, 'material_category_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image
            ? asset('storage/' . $this->cover_image)
            : null;
    }

    public function setCoverImageAttribute($value)
    {
        $attributeName = 'cover_image';
        $disk = 'public';
        $destinationPath = 'material-categories';

        $this->uploadFileToDisk($value, $attributeName, $disk, $destinationPath);
    }
}
