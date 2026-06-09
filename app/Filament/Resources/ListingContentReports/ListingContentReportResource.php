<?php

namespace App\Filament\Resources\ListingContentReports;

use App\Filament\Resources\ListingContentReports\Pages\ListListingContentReports;
use App\Models\ListingContentReport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use UnitEnum;

class ListingContentReportResource extends Resource
{
    protected static ?string $model = ListingContentReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $navigationLabel = 'Listing Reports';

    protected static ?string $modelLabel = 'Listing Report';

    protected static ?string $pluralModelLabel = 'Listing Reports';

    protected static ?string $slug = 'listing-reports';

    protected static string|UnitEnum|null $navigationGroup = 'Support';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ListingContentReport::query()->where('status', ListingContentReport::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Details')
                    ->schema([
                        TextEntry::make('id')->label('Report ID')->prefix('#'),
                        TextEntry::make('created_at')->label('Created Date')->dateTime(),
                        TextEntry::make('status')->badge()->formatStateUsing(fn (string $state): string => ListingContentReport::statusOptions()[$state] ?? $state),
                        TextEntry::make('priority_level')->badge()->color(fn (string $state): string => match ($state) {
                            ListingContentReport::PRIORITY_HIGH => 'danger',
                            ListingContentReport::PRIORITY_MEDIUM => 'warning',
                            default => 'gray',
                        })->formatStateUsing(fn (string $state): string => ListingContentReport::priorityOptions()[$state] ?? $state),
                        TextEntry::make('category')->label('Category')->formatStateUsing(fn (string $state): string => ListingContentReport::categoryOptions()[$state] ?? $state),
                        TextEntry::make('listing_id')->label('Listing ID')->placeholder('Not provided'),
                        TextEntry::make('listing_url')->label('Listing URL')->url(fn ($state) => filled($state) ? $state : null, true)->copyable(),
                        TextEntry::make('advertiser_name')->label('Profile / Advertiser Name'),
                        TextEntry::make('listing_phone')->label('Listing Phone')->placeholder('Not provided'),
                        TextEntry::make('listing_location')->label('City / Location')->placeholder('Not provided'),
                        TextEntry::make('description')->label('Description')->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Reporter Information')
                    ->schema([
                        IconEntry::make('is_anonymous')->label('Anonymous')->boolean(),
                        TextEntry::make('reporter_name')->label('Reporter Name')->placeholder('Anonymous'),
                        TextEntry::make('reporter_email')->label('Reporter Email'),
                        TextEntry::make('reporter_phone')->label('Reporter Phone')->placeholder('Not provided'),
                        IconEntry::make('is_urgent')->label('Urgent Review')->boolean(),
                        IconEntry::make('is_person_shown')->label('Person Shown')->boolean(),
                    ])
                    ->columns(2),
                Section::make('Uploaded Evidence')
                    ->schema([
                        TextEntry::make('uploaded_evidence')
                            ->label('Files')
                            ->formatStateUsing(function (mixed $state): HtmlString|string {
                                if (blank($state)) {
                                    return 'No evidence uploaded';
                                }

                                if (is_string($state)) {
                                    $decoded = json_decode($state, true);
                                    if (is_array($decoded)) {
                                        $state = $decoded;
                                    }
                                }

                                if (! is_array($state)) {
                                    return (string) $state;
                                }

                                $disk = Storage::disk(config('media.upload_disk', 'public'));
                                $items = [];

                                foreach ($state as $path) {
                                    if (! filled($path)) {
                                        continue;
                                    }

                                    $path = (string) $path;
                                    $url = $disk->url($path);
                                    $name = basename($path);
                                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);

                                    if ($isImage) {
                                        $items[] = '<div style="margin-bottom:1rem;">'
                                            .'<a href="'.e($url).'" target="_blank" rel="noopener noreferrer">'
                                            .'<img src="'.e($url).'" alt="'.e($name).'" style="max-width:260px;height:auto;border-radius:0.5rem;border:1px solid #e5e7eb;" />'
                                            .'</a>'
                                            .'</div>';
                                    } else {
                                        $items[] = '<div style="margin-bottom:0.75rem;"><a href="'.e($url).'" target="_blank" rel="noopener noreferrer">'.e($name).'</a></div>';
                                    }
                                }

                                return new HtmlString(
                                    $items === [] ? 'No evidence uploaded' : implode('', $items)
                                );
                            })
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
                Section::make('Admin Notes')
                    ->schema([
                        TextEntry::make('admin_notes')
                            ->label('Notes')
                            ->placeholder('No notes yet')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Report ID')->prefix('#')->sortable(),
                TextColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => ListingContentReport::categoryOptions()[$state] ?? $state)
                    ->wrap(),
                TextColumn::make('advertiser_name')->label('Advertiser')->searchable(),
                TextColumn::make('listing_id')->label('Listing ID')->placeholder('—'),
                TextColumn::make('reporter_email')->label('Reporter Email')->searchable(),
                TextColumn::make('priority_level')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ListingContentReport::PRIORITY_HIGH => 'danger',
                        ListingContentReport::PRIORITY_MEDIUM => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ListingContentReport::priorityOptions()[$state] ?? $state),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ListingContentReport::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        ListingContentReport::STATUS_NEW => 'danger',
                        ListingContentReport::STATUS_UNDER_REVIEW => 'warning',
                        ListingContentReport::STATUS_MORE_INFORMATION_REQUIRED => 'warning',
                        ListingContentReport::STATUS_ACTION_TAKEN => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Created')->since()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('update_status')
                    ->label('Update')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options(ListingContentReport::statusOptions())
                            ->required(),
                        Select::make('priority_level')
                            ->label('Priority')
                            ->options(ListingContentReport::priorityOptions())
                            ->required(),
                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(5)
                            ->maxLength(5000),
                    ])
                    ->fillForm(fn (ListingContentReport $record): array => [
                        'status' => $record->status,
                        'priority_level' => $record->priority_level,
                        'admin_notes' => $record->admin_notes,
                    ])
                    ->action(function (ListingContentReport $record, array $data): void {
                        $record->update($data);

                        Notification::make()
                            ->title('Listing report updated successfully.')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(ListingContentReport::statusOptions()),
                SelectFilter::make('priority_level')->label('Priority')->options(ListingContentReport::priorityOptions()),
                SelectFilter::make('category')->options(ListingContentReport::categoryOptions()),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No listing reports yet')
            ->emptyStateDescription('Submitted listing safety reports will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListingContentReports::route('/'),
        ];
    }
}
