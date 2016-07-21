<?php

namespace App\Models;

use Barryvdh\TranslationManager\Models\Translation as TranslationManagerModel;

class Translation extends TranslationManagerModel
{
    protected $fillable = ['value'];
}
