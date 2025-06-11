<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\School;

class KMeansMap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static string $view = 'filament.clusters.kmeans.pages.kmeans-map';

    protected static ?string $navigationLabel = 'Peta Clustering';

    protected static ?string $title = 'Peta Hasil Clustering';

    protected static ?int $navigationSort = 6;

    protected static ?string $cluster = KMeans::class;

    public $clusterResults = [];
    public $clusterColors = [
        1 => '#FFD700', // Kuning
        2 => '#32CD32', // Hijau
        3 => '#FF0000', // Merah
        4 => '#1E90FF', // Biru
        5 => '#FF1493', // Pink
        6 => '#8A2BE2', // Ungu
        7 => '#FF8C00', // Oranye
        8 => '#20B2AA', // Turquoise
        9 => '#B8860B', // Coklat
        10 => '#FF69B4' // Pink Muda
    ];

    public function mount()
    {
        // Cek apakah hasil clustering tersedia
        $results = Session::get('kmeans_results');

        if (!$results || empty($results['clusters'])) {
            Session::flash('error', 'Data clustering tidak tersedia. Silakan lakukan clustering terlebih dahulu.');
            return redirect('/admin/k-means/clustering');
        }

        // Ambil data cluster
        $this->clusterResults = $results['clusters'];

        // Debug log untuk melihat struktur data clustering
        Log::info('Clustering Results Structure:', [
            'has_results' => !empty($results),
            'cluster_count' => count($this->clusterResults),
            'clusters_size' => array_map('count', $this->clusterResults)
        ]);
    }

    public function getMapData()
    {
        $mapData = [];

        // Loop untuk setiap cluster
        foreach ($this->clusterResults as $clusterIndex => $cluster) {
            foreach ($cluster as $data) {
                $school = \App\Models\School::find($data['school_id']);
                if (!$school || !$school->latitude || !$school->longitude) {
                    Log::warning('Sekolah tanpa koordinat:', [
                        'school_id' => $data['school_id'],
                        'school_name' => $data['school_name']
                    ]);
                    continue;
                }
                $mapData[] = [
                    'lat' => (float) $school->latitude,
                    'lng' => (float) $school->longitude,
                    'title' => $data['school_name'],
                    'cluster' => $clusterIndex + 1,
                    'color' => $this->clusterColors[$clusterIndex + 1],
                    'info' => [
                        'Sekolah' => $data['school_name'],
                        'Kecamatan' => $data['subdistrict_name'],
                        'Tahun' => $data['year_received'],
                        'Dana' => 'Rp ' . number_format($data['amount'], 0, ',', '.'),
                        'Cluster' => 'Cluster ' . ($clusterIndex + 1)
                    ]
                ];
            }
        }

        // Debug log
        Log::info('Total marker di peta:', ['count' => count($mapData)]);
        Log::info('Map Data Summary:', [
            'total_points' => count($mapData),
            'clusters' => array_count_values(array_column($mapData, 'cluster'))
        ]);

        return $mapData;
    }
}
