<?php

namespace App\Filament\Clusters\Settings\Resources\FavIcons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FavIconForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\FileUpload::make('icon_path')
                    ->directory('favicons')
                    ->image()
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
