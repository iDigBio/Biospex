<?php

namespace App\Filament\Resources\ActorWorkflows\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActorWorkflowInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('workflow_id')
                    ->numeric(),
                TextEntry::make('actor_id')
                    ->numeric(),
                TextEntry::make('order')
                    ->numeric(),
            ]);
    }
}
