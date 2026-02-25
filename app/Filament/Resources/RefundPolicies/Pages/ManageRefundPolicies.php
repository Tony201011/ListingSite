<?php

namespace App\Filament\Resources\RefundPolicies\Pages;

use App\Filament\Resources\RefundPolicies\RefundPolicyResource;
use App\Models\RefundPolicy;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRefundPolicies extends ManageRecords
{
    protected static string $resource = RefundPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Refund Policy')
                ->createAnother(false)
                ->visible(fn (): bool => RefundPolicy::query()->doesntExist()),
        ];
    }
}