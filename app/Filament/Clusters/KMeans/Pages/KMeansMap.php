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
        $rawData = Session::get('kmeans_raw_data');

        // Debug raw data
        Log::info('Raw Data:', [
            'count' => count($rawData),
            'sample' => !empty($rawData) ? $rawData[0] : null
        ]);

        // Loop untuk setiap cluster
        foreach ($this->clusterResults as $clusterIndex => $cluster) {
            Log::info("Processing cluster " . ($clusterIndex + 1) . ":", [
                'size' => count($cluster),
                'sample' => !empty($cluster) ? $cluster[0] : null
            ]);

            // Loop untuk setiap data dalam cluster
            foreach ($cluster as $data) {
                // Cari data asli dari rawData berdasarkan index
                $originalData = null;
                foreach ($rawData as $raw) {
                    if ($raw['school_name'] === $data['school_name']) {
                        $originalData = $raw;
                        break;
                    }
                }

                if ($originalData) {
                    // Ambil data sekolah
                    $school = School::find($originalData['school_id']);

                    if ($school && $school->latitude && $school->longitude) {
                        $mapData[] = [
                            'lat' => (float) $school->latitude,
                            'lng' => (float) $school->longitude,
                            'title' => $originalData['school_name'],
                            'cluster' => $clusterIndex + 1,
                            'color' => $this->clusterColors[$clusterIndex + 1],
                            'info' => [
                                'Sekolah' => $originalData['school_name'],
                                'Kecamatan' => $originalData['subdistrict_name'],
                                'Tahun' => $originalData['year_received'],
                                'Dana' => 'Rp ' . number_format($originalData['amount'], 0, ',', '.'),
                                'Cluster' => 'Cluster ' . ($clusterIndex + 1)
                            ]
                        ];

                        Log::info("Added school to map:", [
                            'school' => $originalData['school_name'],
                            'cluster' => $clusterIndex + 1
                        ]);
                    }
                }
            }
        }

        // Debug log
        Log::info('Map Data Summary:', [
            'total_points' => count($mapData),
            'clusters' => array_count_values(array_column($mapData, 'cluster'))
        ]);

        return $mapData;
    }
}
