<?php

namespace App\Filament\Widgets;

use App\Models\KipRecipient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KipStatisticsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Menghitung statistik
        $totalKipRecipients = KipRecipient::count();

        return [
            Stat::make('Total Penerima KIP', $totalKipRecipients)
                ->description('Jumlah seluruh penerima KIP')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),
        ];
    }
}
