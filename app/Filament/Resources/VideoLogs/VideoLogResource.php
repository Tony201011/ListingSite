<?php

namespace App\Filament\Resources\VideoLogs;

use App\Filament\Clusters\Logs;
use App\Filament\Resources\VideoLogs\Pages\ListVideoLogs;
use App\Models\SiteSetting;
use App\Models\UserVideo;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VideoLogResource extends Resource
{
    protected static ?string $model = UserVideo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?string $navigationLabel = 'Video Logs';

    protected static ?string $modelLabel = 'Video Log';

    protected static ?string $pluralModelLabel = 'Video Logs';

    protected static ?string $slug = 'video-logs';

    protected static ?string $cluster = Logs::class;

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin'
            && SiteSetting::isLoggingEnabled();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('providerProfile.name')
                    ->label('Profile')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('original_name')
                    ->label('File Name')
                    ->limit(50)
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('video_path')
                    ->label('Video Path')
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime()
                    ->since()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->since()
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No video upload records yet')
            ->emptyStateDescription('Video upload activity will appear here once users add videos.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVideoLogs::route('/'),
        ];
    }
}
