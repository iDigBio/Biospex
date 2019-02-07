<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;
use App\Models\Traits\PivotEventTrait;

class BaseEloquentModel extends Model
{
    use LadaCacheTrait, PivotEventTrait;
}