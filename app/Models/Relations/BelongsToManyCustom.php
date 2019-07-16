<?php
/**
 * Not using package until https://github.com/fico7489/laravel-pivot/issues/55 is fixed
 */

namespace App\Models\Relations;

use App\Models\Traits\FiresPivotEventsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyCustom extends BelongsToMany
{
    use FiresPivotEventsTrait;
}