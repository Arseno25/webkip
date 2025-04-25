<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use App\Models\Subdistrict;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchool extends EditRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    public function getSubdistrictBoundaries($subdistrictId = null)
    {
        if (!$subdistrictId) {
            return null;
        }
        
        $subdistrict = Subdistrict::find($subdistrictId);
        
        if ($subdistrict && $subdistrict->boundaries) {
            return $subdistrict->boundaries;
        }
        
        return null;
    }
}
