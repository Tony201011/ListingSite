<?php

namespace App\Filament\Clusters\Settings\Resources\MetaKeywords\Pages;

use App\Filament\Concerns\ReviewerReadOnly;
use App\Filament\Clusters\Settings\Resources\MetaKeywords\MetaKeywordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMetaKeyword extends CreateRecord
{
    use ReviewerReadOnly;

    protected static string $resource = MetaKeywordResource::class;
}
