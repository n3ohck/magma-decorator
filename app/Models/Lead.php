<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use CrudTrait;

    protected $fillable = [
        'design_session_id',
        'name',
        'email',
        'phone',
        'city',
        'project_type',
        'preferred_contact_method',
        'message',
        'status',
    ];

    protected $casts = [
        'design_session_id' => 'integer',
    ];

    public function designSession()
    {
        return $this->belongsTo(DesignSession::class);
    }
}
