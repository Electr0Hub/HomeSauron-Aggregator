<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Camera extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'settings',
    ];

    protected function casts()
    {
        return [
            'settings' => 'json',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        self::created(function (Camera $camera) {
            foreach ($camera->settings as $key => $value) {
                static::setSettingCache($camera->id, $key, $value);
            }
        });
    }

    public static function setSettingCache(int $cameraId, string $key, string $value): void
    {
        Cache::forever('camera_' . $cameraId . '_' . $key, $value);
    }

    public static function getSettingCache(int $cameraId, string $key, mixed $default = null): mixed
    {
        $value = Cache::get('camera_' . $cameraId . '_' . $key);

        if(is_null($value)) {
            $settings = static::select('settings')->whereId($cameraId)->first()->settings;

            if(!isset($settings[$key])) {
                return $default;
            }
            else {
                $value = $settings[$key];
                static::setSettingCache($cameraId, $key, $value);
                return $value;
            }
        }

        return $value;
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
