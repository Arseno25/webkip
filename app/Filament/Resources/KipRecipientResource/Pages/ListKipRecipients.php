<?php

namespace App\Filament\Resources\KipRecipientResource\Pages;

use App\Filament\Resources\KipRecipientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKipRecipients extends ListRecords
{
    protected static string $resource = KipRecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
