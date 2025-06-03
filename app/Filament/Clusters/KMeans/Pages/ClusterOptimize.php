<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use App\Helpers\KMeansHelper;

class ClusterOptimize extends Page
{
    protected static ?string $title = 'Optimasi Jumlah Cluster';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.clusters.k-means.pages.cluster-optimize';

    protected static ?string $cluster = KMeans::class;

    public $k;
    public $maxIterations;
    public $centroidType;
    public $optimalK;
    public $silhouetteScore;
    public $wcss = [];
    public $silhouetteScores = [];

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

        try {
            // Transformasi data untuk clustering
            $rows = array_map(function ($row) {
                return [
                    floatval($row['school_id']),
                    floatval($row['subdistrict_id']),
                    floatval($row['year_received']),
                    floatval($row['amount']),
                    floatval($row['recipient'])
                ];
            }, $data);

            // Proses optimasi untuk mencari nilai K optimal
            $minK = 2;
            $maxK = 10;
            $this->wcss = [];
            $this->silhouetteScores = [];

            for ($k = $minK; $k <= $maxK; $k++) {
                $result = KMeansHelper::kmeans($rows, $k, $this->maxIterations);
                $clusters = $result['clusters'];
                $centroids = $result['centroids'];

                // Hitung WCSS
                $wcss = KMeansHelper::calculateWCSS($clusters, $centroids);
                $this->wcss[$k] = $wcss;

                // Hitung Silhouette Score
                $silhouetteScore = KMeansHelper::calculateSilhouetteScore($rows, $clusters);
                $this->silhouetteScores[$k] = $silhouetteScore;
            }

            // Tentukan nilai K optimal berdasarkan Silhouette Score tertinggi
            $this->optimalK = array_search(max($this->silhouetteScores), $this->silhouetteScores);
            $this->silhouetteScore = $this->silhouetteScores[$this->optimalK];

            // Simpan nilai K optimal ke session
            Session::put('kmeans_optimal_k', $this->optimalK);
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
            return redirect('/admin/k-means/dataset');
        }
    }

    public function goToClustering()
    {
        return redirect('/admin/k-means/clustering');
    }
}
