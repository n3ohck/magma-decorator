<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    use CrudTrait;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'keywords',
        'base_image',
        'preview_image',
        'shadow_overlay_image',
        'light_overlay_image',
        'canvas_width',
        'canvas_height',
        'is_featured',
        'is_active',
        'all_materials',
        'sort_order',
        'foreground_overlay_image',
    ];

    protected $casts = [
        'keywords'    => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'all_materials' => 'boolean',
        'sort_order' => 'integer',
        'canvas_width' => 'integer',
        'canvas_height' => 'integer',
    ];

    protected $appends = [
        'base_image_url',
        'preview_image_url',
        'shadow_overlay_url',
        'light_overlay_url',
        'foreground_overlay_url',
    ];

    public function zones()
    {
        return $this->hasMany(EnvironmentZone::class)
            ->orderBy('sort_order');
    }

    public function activeZones()
    {
        return $this->hasMany(EnvironmentZone::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function zoneGroups()
    {
        return $this->hasMany(EnvironmentZoneGroup::class)->orderBy('sort_order');
    }

    public function activeZoneGroups()
    {
        return $this->hasMany(EnvironmentZoneGroup::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function designSessions()
    {
        return $this->hasMany(DesignSession::class);
    }

    /**
     * Materiales habilitados para este ambiente (cuando all_materials = false).
     */
    public function materials()
    {
        return $this->belongsToMany(Material::class, 'environment_material');
    }

    public function getBaseImageUrlAttribute()
    {
        return $this->base_image
            ? asset('storage/' . $this->base_image)
            : null;
    }

    public function getPreviewImageUrlAttribute()
    {
        if ($this->preview_image) {
            return asset('storage/' . $this->preview_image);
        }

        return $this->base_image_url;
    }

    public function getShadowOverlayUrlAttribute()
    {
        return $this->shadow_overlay_image
            ? asset('storage/' . $this->shadow_overlay_image)
            : null;
    }

    public function getLightOverlayUrlAttribute()
    {
        return $this->light_overlay_image
            ? asset('storage/' . $this->light_overlay_image)
            : null;
    }

    public function setBaseImageAttribute($value)
    {
        $attributeName = 'base_image';
        $disk = 'public';
        $destinationPath = 'environments/base';

        $this->uploadFileToDisk($value, $attributeName, $disk, $destinationPath);
    }

    public function setPreviewImageAttribute($value)
    {
        $attributeName = 'preview_image';
        $disk = 'public';
        $destinationPath = 'environments/previews';

        $this->uploadFileToDisk($value, $attributeName, $disk, $destinationPath);
    }

    public function setShadowOverlayImageAttribute($value)
    {
        $attributeName = 'shadow_overlay_image';
        $disk = 'public';
        $destinationPath = 'environments/overlays/shadows';

        $this->uploadFileToDisk($value, $attributeName, $disk, $destinationPath);
    }

    public function setLightOverlayImageAttribute($value)
    {
        $attributeName = 'light_overlay_image';
        $disk = 'public';
        $destinationPath = 'environments/overlays/lights';

        $this->uploadFileToDisk($value, $attributeName, $disk, $destinationPath);
    }

    public function getForegroundOverlayUrlAttribute()
    {
        return $this->foreground_overlay_image
            ? asset('storage/' . $this->foreground_overlay_image)
            : null;
    }
}
