<?php

namespace App\Filament\Resources\GroupInvites\Pages;

use App\Filament\Resources\GroupInvites\GroupInviteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGroupInvites extends ListRecords
{
    protected static string $resource = GroupInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
