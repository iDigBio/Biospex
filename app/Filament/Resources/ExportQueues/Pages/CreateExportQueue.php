<?php

namespace App\Filament\Resources\ExportQueues\Pages;

use App\Filament\Resources\ExportQueues\ExportQueueResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExportQueue extends CreateRecord
{
    protected static string $resource = ExportQueueResource::class;
}
