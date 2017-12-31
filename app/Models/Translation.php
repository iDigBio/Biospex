<?php

namespace App\Models;

use Barryvdh\TranslationManager\Models\Translation as TranslationManagerModel;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Translation extends TranslationManagerModel
{
    use LadaCacheTrait;

    protected $fillable = ['value'];
}
