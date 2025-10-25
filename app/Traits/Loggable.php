<?php

namespace App\Traits;

use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    public static function bootLoggable()
    {
        static::created(function ($model) {
            if (Auth::check()) {
                ActivityLogService::logCreate(
                    get_class($model), 
                    $model->getKey(), 
                    ['attributes' => $model->getAttributes()]
                );
            }
        });

        static::updated(function ($model) {
            if (Auth::check()) {
                ActivityLogService::logUpdate(
                    get_class($model), 
                    $model->getKey(), 
                    ['attributes' => $model->getDirty()]
                );
            }
        });

        static::deleted(function ($model) {
            if (Auth::check()) {
                ActivityLogService::logDelete(
                    get_class($model), 
                    $model->getKey(), 
                    ['attributes' => $model->getAttributes()]
                );
            }
        });
    }
}