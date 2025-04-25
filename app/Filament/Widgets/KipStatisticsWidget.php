<?php

namespace App\Filament\Widgets;

use App\Models\KipRecipient;
use App\Models\School;
use App\Models\Subdistrict;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KipStatisticsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Menghitung statistik
        $totalKipRecipients = KipRecipient::count();
        $totalSchools = School::count();
        $totalSubdistricts = Subdistrict::count();
        
        // Menghitung jumlah penerima KIP berdasarkan jenis kelamin
        $maleCount = KipRecipient::where('gender', 'L')->count();
        $femaleCount = KipRecipient::where('gender', 'P')->count();
        
        return [
            Stat::make('Total Penerima KIP', $totalKipRecipients)
                ->description('Jumlah seluruh penerima KIP')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),
            Stat::make('Total Sekolah', $totalSchools)
                ->description('Jumlah seluruh sekolah')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make('Total Kecamatan', $totalSubdistricts)
                ->description('Jumlah seluruh kecamatan')
                ->descriptionIcon('heroicon-m-map')
                ->color('warning'),
            Stat::make('Laki-laki', $maleCount)
                ->description('Jumlah penerima KIP laki-laki')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),
            Stat::make('Perempuan', $femaleCount)
                ->description('Jumlah penerima KIP perempuan')
                ->descriptionIcon('heroicon-m-user')
                ->color('danger'),
        ];
    }
}
