<?php

namespace App\Models\Traits;

trait UuidTrait
{
    /**
     * Boot the Uuid trait for the model.
     */
    public static function bootUuidTrait(): void
    {
        static::creating(function ($model) {
            $model->uuid = \Str::uuid();
        });
    }
}
