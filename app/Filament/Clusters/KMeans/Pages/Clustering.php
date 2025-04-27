<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use Filament\Pages\Page;

class Clustering extends Page
{
    protected static ?string $title = 'Hasil K-Means Clustering';
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.clusters.k-means.pages.clustering';

    protected static ?string $cluster = KMeans::class;

    public $dataWithCluster = [];
    public $header = [];
    public $centroids = [];
    public $summary = [];

    public function mount()
    {
        $result = session('kmeans_result', []);
        $header = session('kmeans_header', []);
        $names = session('uploaded_names', []);

        if (empty($result) || empty($result['clusters'])) {
            session()->flash('error', 'Belum ada hasil clustering. Silakan proses K-Means terlebih dahulu.');
            $this->redirect('/admin/k-means/cluster-processing');
            return;
        }

        // Ambil header numerik (skip kolom pertama)
        $numericHeader = array_slice($header, 1);

        // Gabungkan nama, data numerik, dan cluster
        $dataWithCluster = [];
        $rowIndex = 0;
        foreach ($result['clusters'] as $clusterIdx => $cluster) {
            foreach ($cluster as $point) {
                $row = [];
                $row['nama'] = $names[$rowIndex] ?? '-';
                // Pastikan jumlah kolom sama sebelum array_combine
                if (count($numericHeader) !== count($point)) {
                    $rowIndex++;
                    continue;
                }
                $row += array_combine($numericHeader, $point);
                $row['cluster'] = $clusterIdx + 1;
                $dataWithCluster[] = $row;
                $rowIndex++;
            }
        }
        $this->dataWithCluster = $dataWithCluster;
        $this->header = array_merge(['nama'], $numericHeader, ['cluster']);
        $this->centroids = $result['centroids'];
    }
}
