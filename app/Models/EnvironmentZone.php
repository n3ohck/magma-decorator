<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class EnvironmentZone extends Model
{
    use CrudTrait;

    protected $fillable = [
        'environment_id',
        'zone_group_id',
        'name',
        'slug',
        'zone_type',
        'mask_image',
        'polygon_points',
        'default_texture_scale',
        'default_texture_rotation',
        'default_opacity',
        'supports_perspective',
        'default_book_match',
        'perspective_points',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'environment_id' => 'integer',
        'zone_group_id'  => 'integer',
        'polygon_points' => 'array',
        'perspective_points' => 'array',
        'supports_perspective' => 'boolean',
        'default_book_match' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'default_texture_scale' => 'decimal:2',
        'default_texture_rotation' => 'decimal:2',
        'default_opacity' => 'decimal:2',
    ];

    protected $appends = [
        'mask_image_url',
    ];

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    public function zoneGroup()
    {
        return $this->belongsTo(EnvironmentZoneGroup::class, 'zone_group_id');
    }

    public function designSessionItems()
    {
        return $this->hasMany(DesignSessionItem::class);
    }

    public function getMaskImageUrlAttribute()
    {
        return $this->mask_image
            ? asset('storage/' . $this->mask_image)
            : null;
    }

    public function setMaskImageAttribute($value)
    {
        $attributeName = 'mask_image';
        $disk = 'public';
        $destinationPath = 'environments/masks';

        $this->uploadFileToDisk($value, $attributeName, $disk, $destinationPath);
    }
}
