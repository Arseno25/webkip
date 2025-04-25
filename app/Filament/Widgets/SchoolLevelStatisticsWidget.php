<?php

namespace App\Filament\Widgets;

use App\Models\KipRecipient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SchoolLevelStatisticsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Menghitung jumlah penerima KIP berdasarkan jenjang sekolah
        $sdCount = KipRecipient::whereHas('school', function ($query) {
            $query->where('level', 'SD');
        })->count();
        
        $smpCount = KipRecipient::whereHas('school', function ($query) {
            $query->where('level', 'SMP');
        })->count();
        
        $smaCount = KipRecipient::whereHas('school', function ($query) {
            $query->where('level', 'SMA');
        })->count();
        
        $smkCount = KipRecipient::whereHas('school', function ($query) {
            $query->where('level', 'SMK');
        })->count();
        
        return [
            Stat::make('SD', $sdCount)
                ->description('Penerima KIP di SD')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make('SMP', $smpCount)
                ->description('Penerima KIP di SMP')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
            Stat::make('SMA', $smaCount)
                ->description('Penerima KIP di SMA')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),
            Stat::make('SMK', $smkCount)
                ->description('Penerima KIP di SMK')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('danger'),
        ];
    }
}
