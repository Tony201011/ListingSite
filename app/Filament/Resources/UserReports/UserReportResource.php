<?php

namespace App\Filament\Resources\UserReports;

use App\Filament\Clusters\Pages;
use App\Filament\Resources\UserReports\Pages\ListUserReports;
use App\Models\UserReport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserReportResource extends Resource
{
    protected static ?string $model = UserReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationLabel = 'User Reports';

    protected static ?string $modelLabel = 'User Report';

    protected static ?string $pluralModelLabel = 'User Reports';

    protected static ?string $slug = 'user-reports';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 11;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
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
                        TextEntry::make('reporter_email')
                            ->label('Reporter Email')
                            ->placeholder('Not provided'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Admin Reply')
                    ->schema([
                        TextEntry::make('admin_reply')
                            ->label('Reply sent')
                            ->placeholder('No reply sent yet')
                            ->columnSpanFull(),
                        TextEntry::make('replied_at')
                            ->label('Replied at')
                            ->dateTime()
                            ->placeholder('Not replied yet'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'reviewed' => 'success',
                                'dismissed' => 'gray',
                                default => 'warning',
                            }),
                    ])
                    ->columns(2),
                Section::make('Meta')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Reported at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsed(),
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
                TextColumn::make('reporter_name')
                    ->label('Reporter')
                    ->placeholder('Anonymous')
                    ->searchable(),
                TextColumn::make('reporter_email')
                    ->label('Reporter Email')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reviewed' => 'success',
                        'dismissed' => 'gray',
                        default => 'warning',
                    }),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Reported')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->after(fn (UserReport $record): bool => $record->is_read ?: (bool) $record->update(['is_read' => true])),

                Action::make('mark_read')
                    ->label('Mark read')
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->visible(fn (UserReport $record): bool => ! $record->is_read)
                    ->requiresConfirmation(false)
                    ->action(fn (UserReport $record): bool => $record->update(['is_read' => true])),

                Action::make('mark_reviewed')
                    ->label('Mark reviewed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (UserReport $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Reviewed')
                    ->modalDescription('Mark this report as reviewed?')
                    ->action(fn (UserReport $record): bool => $record->update(['status' => 'reviewed', 'is_read' => true])),

                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (UserReport $record): bool => $record->status !== 'dismissed')
                    ->requiresConfirmation()
                    ->modalHeading('Dismiss Report')
                    ->modalDescription('Are you sure you want to dismiss this report?')
                    ->action(fn (UserReport $record): bool => $record->update(['status' => 'dismissed', 'is_read' => true])),

                DeleteAction::make()->requiresConfirmation(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'dismissed' => 'Dismissed',
                    ]),
                TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No user reports yet')
            ->emptyStateDescription('Submitted profile reports will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserReports::route('/'),
        ];
    }
}
