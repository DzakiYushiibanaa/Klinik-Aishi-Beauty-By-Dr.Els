<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Product')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->slideOver()
                ->stickyModalFooter()
                ->modalHeading('Create Product')
                ->modalWidth('xl')
                ->modalSubmitActionLabel('Create')
                ->modalCancelActionLabel('Cancel')
                ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
                ->createAnother(true),
        ];
    }
}
