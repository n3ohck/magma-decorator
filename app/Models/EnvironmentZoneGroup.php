<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class EnvironmentZoneGroup extends Model
{
    use CrudTrait;

    protected $fillable = [
        'environment_id',
        'name',
        'slug',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'environment_id' => 'integer',
        'is_active'      => 'boolean',
        'sort_order'     => 'integer',
    ];

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    public function zones()
    {
        return $this->hasMany(EnvironmentZone::class, 'zone_group_id')->orderBy('sort_order');
    }

    public function activeZones()
    {
        return $this->hasMany(EnvironmentZone::class, 'zone_group_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
