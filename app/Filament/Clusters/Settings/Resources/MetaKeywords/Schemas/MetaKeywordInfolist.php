<?php

namespace App\Filament\Clusters\Settings\Resources\MetaKeywords\Schemas;

use App\Models\MetaKeyword;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MetaKeywordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('page_name'),
                TextEntry::make('meta_keyword')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (MetaKeyword $record): bool => $record->trashed()),
            ]);
    }
}
