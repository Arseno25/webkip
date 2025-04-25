<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use App\Models\Subdistrict;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSchool extends CreateRecord
{
    protected static string $resource = SchoolResource::class;
    
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
