<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class KMeans extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'K-Means';

    protected static ?int $navigationSort = 20;
}
