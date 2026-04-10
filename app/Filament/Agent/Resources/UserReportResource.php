<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\UserReportResource\Pages\ListUserReports;
use App\Models\UserReport;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserReportResource extends Resource
{
    protected static ?string $model = UserReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationLabel = 'Profile Reports';

    protected static ?string $modelLabel = 'Profile Report';

    protected static ?string $pluralModelLabel = 'Profile Reports';

    protected static ?string $slug = 'profile-reports';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $agentId = Filament::auth()->id();

        return parent::getEloquentQuery()
            ->whereHas('providerProfile', fn (Builder $q) => $q->where('agent_id', $agentId));
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Details')
                    ->schema([
                        TextEntry::make('providerProfile.name')
                            ->label('Reported Profile')
                            ->placeholder('Unknown'),
                        TextEntry::make('reason')
                            ->label('Reason')
                            ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                        TextEntry::make('reporter_name')
                            ->label('Reporter Name')
                            ->placeholder('Anonymous'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'reviewed' => 'success',
                                'dismissed' => 'gray',
                                default => 'warning',
                            }),
                        TextEntry::make('created_at')
                            ->label('Reported at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('providerProfile.name')
                    ->label('Reported Profile')
                    ->placeholder('—')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reviewed' => 'success',
                        'dismissed' => 'gray',
                        default => 'warning',
                    }),
                IconColumn::make('is_read')
                    ->label('Reviewed by admin')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Reported')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'dismissed' => 'Dismissed',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No reports yet')
            ->emptyStateDescription('Reports submitted against your managed profiles will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserReports::route('/'),
        ];
    }
}
