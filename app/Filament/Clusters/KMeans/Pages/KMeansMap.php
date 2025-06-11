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

        // Debug log
        Log::info('Clustering Results:', [
            'has_results' => !empty($results),
            'cluster_count' => count($this->clusterResults),
            'first_cluster' => !empty($this->clusterResults) ? count($this->clusterResults[0]) : 0,
            'sample_data' => !empty($this->clusterResults) ? $this->clusterResults[0][0] : null
        ]);
    }

    public function getMapData()
    {
        $mapData = [];

        foreach ($this->clusterResults as $clusterIndex => $cluster) {
            Log::info("Processing cluster {$clusterIndex}:", [
                'cluster_size' => count($cluster),
                'sample_data' => !empty($cluster) ? $cluster[0] : null
            ]);

            foreach ($cluster as $data) {
                // Debug log untuk setiap data
                Log::info("Processing data:", [
                    'school_id' => $data['school_id'] ?? 'not set',
                    'school_name' => $data['school_name'] ?? 'not set'
                ]);

                // Ambil data sekolah berdasarkan school_id
                $school = School::find($data['school_id']);

                if ($school) {
                    Log::info("School found:", [
                        'id' => $school->id,
                        'name' => $school->name,
                        'latitude' => $school->latitude,
                        'longitude' => $school->longitude
                    ]);
                } else {
                    Log::info("School not found for ID: " . ($data['school_id'] ?? 'not set'));
                }

                if ($school && $school->latitude && $school->longitude) {
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
        }

        // Debug log
        Log::info('Map Data:', [
            'total_points' => count($mapData),
            'first_point' => !empty($mapData) ? $mapData[0] : null
        ]);

        return $mapData;
    }
}
