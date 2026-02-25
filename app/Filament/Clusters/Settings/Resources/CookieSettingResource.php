<?php

// namespace App\Filament\Clusters\Settings\Resources;

// use App\Models\CookieSetting;
// use Filament\Resources\Resource;
// use Filament\Resources\Pages\ListRecords;
// use Filament\Resources\Pages\EditRecord;
// use Filament\Resources\Pages\CreateRecord;
// use Filament\Forms;
// use Filament\Tables;
// use Filament\Schemas\Schema;
// use BackedEnum;

// class CookieSettingResource extends Resource
// {
//     protected static ?string $model = CookieSetting::class;
//     protected static ?string $navigationLabel = 'Cookie Settings';
//     protected static ?string $cluster = \App\Filament\Clusters\Settings::class;
//     // Removed invalid icon reference

//     public static function form(Schema $schema): Schema
//     {
//         return $schema->components([
//             Forms\Components\TextInput::make('name')->label('Cookie Name')->required(),
//             Forms\Components\Textarea::make('description')->label('Description')->rows(3),
//             Forms\Components\Toggle::make('is_active')->label('Active'),
//         ]);
//     }

//     public static function table(Tables\Table $table): Tables\Table
//     {
//         return $table->columns([
//             Tables\Columns\TextColumn::make('name')->label('Cookie Name'),
//             Tables\Columns\TextColumn::make('description')->label('Description')->limit(40),
//             Tables\Columns\BooleanColumn::make('is_active')->label('Active'),
//         ]);
//     }

//     public static function getPages(): array
//     {
//         return [
//             'index' => \App\Filament\Clusters\Settings\Resources\CookieSettingResource\Pages\ListCookieSettings::route('/'),
//             'create' => \App\Filament\Clusters\Settings\Resources\CookieSettingResource\Pages\CreateCookieSetting::route('/create'),
//             'edit' => \App\Filament\Clusters\Settings\Resources\CookieSettingResource\Pages\EditCookieSetting::route('/{record}/edit'),
//         ];
//     }
// }
