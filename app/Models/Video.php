<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'camera_id',
        'name',
        'absolute_path',
    ];

    public const UPDATED_AT = null;
}
