<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    const GOOGLE_DRIVE_ACCESS_TOKEN = 'GOOGLE_DRIVE_ACCESS_TOKEN';

    protected $primaryKey = 'key';
    public $incrementing = false;

    protected $fillable = [
        'key',
        'value'
    ];

    public static function getValue($key)
    {
        return self::where('key', $key)->value('value');
    }
}
