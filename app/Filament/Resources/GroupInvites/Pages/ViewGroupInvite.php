<?php

namespace App\Filament\Resources\GroupInvites\Pages;

use App\Filament\Resources\GroupInvites\GroupInviteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGroupInvite extends ViewRecord
{
    protected static string $resource = GroupInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
