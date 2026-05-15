<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class DesignSession extends Model
{
    use CrudTrait;

    protected $fillable = [
        'environment_id',
        'public_uuid',
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'status',
        'final_image',
        'snapshot',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'environment_id' => 'integer',
        'snapshot' => 'array',
    ];

    protected $appends = [
        'final_image_url',
    ];

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    public function items()
    {
        return $this->hasMany(DesignSessionItem::class);
    }

    public function lead()
    {
        return $this->hasOne(Lead::class);
    }

    public function getFinalImageUrlAttribute()
    {
        return $this->final_image
            ? asset('storage/' . $this->final_image)
            : null;
    }
}
