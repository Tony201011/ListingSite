<?php

namespace App\Filament\Clusters\Settings\Resources\MetaDescriptions\Pages;

use App\Filament\Concerns\ReviewerReadOnly;
use App\Filament\Clusters\Settings\Resources\MetaDescriptions\MetaDescriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMetaDescription extends CreateRecord
{
    use ReviewerReadOnly;

    protected static string $resource = MetaDescriptionResource::class;
}
