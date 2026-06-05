<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use CrudTrait;

    protected $fillable = [
        'material_category_id',
        'name',
        'slug',
        'sku',
        'origin_country',
        'finish',
        'base_color',
        'short_description',
        'description',
        'keywords',
        'texture_image',
        'thumbnail_image',
        'default_scale',
        'default_rotation',
        'default_opacity',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'material_category_id' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'keywords'      => 'array',
        'default_scale' => 'decimal:2',
        'default_rotation' => 'decimal:2',
        'default_opacity' => 'decimal:2',
    ];

    protected $appends = [
        'texture_url',
        'thumbnail_url',
    ];

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'material_category_id');
    }

    public function designSessionItems()
    {
        return $this->hasMany(DesignSessionItem::class);
    }

    public function getTextureUrlAttribute()
    {
        return $this->texture_image
            ? asset('storage/' . $this->texture_image)
            : null;
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_image) {
            return asset('storage/' . $this->thumbnail_image);
        }

        return $this->texture_url;
    }

    public function setTextureImageAttribute($value)
    {
        $attributeName = 'texture_image';
        $disk = 'public';
        $destinationPath = 'materials/textures';

        $this->uploadFileToDisk($value, $attributeName, $disk, $destinationPath);
    }

    public function setThumbnailImageAttribute($value)
    {
        $attributeName = 'thumbnail_image';
        $disk = 'public';
        $destinationPath = 'materials/thumbnails';

        $this->uploadFileToDisk($value, $attributeName, $disk, $destinationPath);
    }
}
