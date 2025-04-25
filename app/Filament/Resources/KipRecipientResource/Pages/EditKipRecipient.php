<?php

namespace App\Filament\Resources\KipRecipientResource\Pages;

use App\Filament\Resources\KipRecipientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKipRecipient extends EditRecord
{
    protected static string $resource = KipRecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
