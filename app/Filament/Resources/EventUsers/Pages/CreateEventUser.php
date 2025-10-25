<?php

namespace App\Filament\Resources\EventUsers\Pages;

use App\Filament\Resources\EventUsers\EventUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventUser extends CreateRecord
{
    protected static string $resource = EventUserResource::class;
}
