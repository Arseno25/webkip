<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\KipRecipient;
use App\Models\School;
use App\Models\Subdistrict;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('map')
                ->label('Lihat Peta')
                ->icon('heroicon-o-map')
                ->url(fn () => route('filament.admin.resources.reports.map')),
                
            Actions\Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('filament.admin.resources.reports.export')),
        ];
    }
}
