<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use App\Helpers\KMeansHelper;

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
    public $rows = [];
    public $k;
    public $maxIterations;
    public $centroidType;
    public $clusters = [];
    public $iterations = 0;
    public $wcss = 0;
    public $silhouetteScore = 0;

    public function mount()
    {
        // Ambil data dari session
        $data = Session::get('kmeans_data');
        $k = Session::get('kmeans_k');
        $maxIterations = Session::get('kmeans_max_iterations');
        $centroidType = Session::get('kmeans_centroid_type');

        if (!$data || !$k || !$maxIterations || !$centroidType) {
            Session::flash('error', 'Data clustering tidak lengkap. Silakan mulai dari awal.');
            return redirect('/admin/k-means/dataset');
        }

        $this->k = $k;
        $this->maxIterations = $maxIterations;
        $this->centroidType = $centroidType;

        // Transformasi data untuk ditampilkan
        $this->header = array_keys($data[0]);
        $this->rows = array_map(function ($row) {
            return array_values($row);
        }, $data);

        try {
            // Proses K-Means dengan nilai K yang sudah dioptimasi
            $result = KMeansHelper::kmeans($this->rows, $this->k, $this->maxIterations);
            $this->clusters = $result['clusters'];
            $this->centroids = $result['centroids'];
            $this->iterations = $result['iterations'];
            $this->wcss = KMeansHelper::calculateWCSS($this->clusters, $this->centroids);
            $this->silhouetteScore = KMeansHelper::calculateSilhouetteScore($this->rows, $this->clusters);

            // Transform hasil clustering untuk ditampilkan
            $this->dataWithCluster = [];
            $rowIndex = 0;
            foreach ($this->clusters as $clusterIndex => $cluster) {
                foreach ($cluster as $point) {
                    $row = [];
                    $row['school'] = $data[$rowIndex]['school'] ?? 'Unknown';
                    $row['subdistrict'] = $data[$rowIndex]['subdistrict'] ?? 'Unknown';
                    $row['year_received'] = $point[2];
                    $row['amount'] = $point[3];
                    $row['recipient'] = $point[4];
                    $row['cluster'] = $clusterIndex + 1;
                    $this->dataWithCluster[] = $row;
                    $rowIndex++;
                }
            }
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
            return redirect('/admin/k-means/dataset');
        }
    }

    public function goToOptimize()
    {
        return redirect('/admin/k-means/cluster-optimize');
    }
}
