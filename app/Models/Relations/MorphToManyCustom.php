<?php
/**
 * Not using package until https://github.com/fico7489/laravel-pivot/issues/55 is fixed
 */
namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Traits\FiresPivotEventsTrait;

class MorphToManyCustom extends MorphToMany
{
    use FiresPivotEventsTrait;
}