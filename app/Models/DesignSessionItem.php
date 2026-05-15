<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class DesignSessionItem extends Model
{
    use CrudTrait;

    protected $fillable = [
        'design_session_id',
        'environment_zone_id',
        'material_id',
        'texture_scale',
        'texture_rotation',
        'opacity',
        'settings',
    ];

    protected $casts = [
        'design_session_id' => 'integer',
        'environment_zone_id' => 'integer',
        'material_id' => 'integer',
        'texture_scale' => 'decimal:2',
        'texture_rotation' => 'decimal:2',
        'opacity' => 'decimal:2',
        'settings' => 'array',
    ];

    public function designSession()
    {
        return $this->belongsTo(DesignSession::class);
    }

    public function environmentZone()
    {
        return $this->belongsTo(EnvironmentZone::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
