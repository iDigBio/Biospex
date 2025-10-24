<?php

namespace App\Filament\Resources\FailedJobs;

use App\Filament\Resources\FailedJobs\Pages\CreateFailedJob;
use App\Filament\Resources\FailedJobs\Pages\EditFailedJob;
use App\Filament\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Filament\Resources\FailedJobs\Pages\ViewFailedJob;
use App\Filament\Resources\FailedJobs\Schemas\FailedJobForm;
use App\Filament\Resources\FailedJobs\Schemas\FailedJobInfolist;
use App\Filament\Resources\FailedJobs\Tables\FailedJobsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\FailedJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FailedJobResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = FailedJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return FailedJobForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FailedJobInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FailedJobsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFailedJobs::route('/'),
            'create' => CreateFailedJob::route('/create'),
            'view' => ViewFailedJob::route('/{record}'),
            'edit' => EditFailedJob::route('/{record}/edit'),
        ];
    }
}
