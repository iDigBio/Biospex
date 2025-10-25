<?php

namespace App\Filament\Resources\GroupInvites\Pages;

use App\Filament\Resources\GroupInvites\GroupInviteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGroupInvite extends EditRecord
{
    protected static string $resource = GroupInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
