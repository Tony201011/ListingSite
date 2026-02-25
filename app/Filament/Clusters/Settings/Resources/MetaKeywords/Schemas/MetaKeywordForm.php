<?php

namespace App\Filament\Clusters\Settings\Resources\MetaKeywords\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MetaKeywordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('page_name')
                    ->required(),
                \Filament\Forms\Components\TagsInput::make('meta_keyword')
                    ->label('Meta Keywords')
                    ->placeholder('Add a keyword and press Enter')
                    ->splitKeys(['Enter', ',']),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
