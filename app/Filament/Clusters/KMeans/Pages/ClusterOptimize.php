<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use App\Helpers\KMeansHelper;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class ClusterOptimize extends Page
{
    protected static string $view = 'filament.clusters.k-means.pages.cluster-optimize';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Optimasi Cluster';
    protected static ?string $title = 'Optimasi Jumlah Cluster';
    protected static ?int $navigationSort = 2;
    protected static ?string $cluster = KMeans::class;

    public $optimalK;
    public $silhouetteScore;
    public $wcss = [];
    public $silhouetteScores = [];
    public $maxIterations = 100;
    public $centroidType = 'kmeans++';

    public function mount()
    {
        try {
            // Ambil data dari session
            $data = Session::get('kmeans_data');
            if (!$data) {
                Session::flash('error', 'Data clustering tidak tersedia. Silakan mulai dari awal.');
                return redirect('/admin/k-means/dataset');
            }

            // Validasi jumlah data
            $numData = count($data);
            if ($numData < 2) {
                throw new \Exception('Minimal harus ada 2 data untuk melakukan clustering.');
            }

            // Untuk data yang sedikit, langsung gunakan K=2
            if ($numData == 2) {
                $this->optimalK = 2;
                $result = KMeansHelper::kmeans($data, 2, $this->maxIterations, $this->centroidType);

                $this->wcss = [2 => KMeansHelper::calculateWCSS($data, $result['clusters'], $result['centroids'])];
                $this->silhouetteScores = [2 => KMeansHelper::calculateSilhouetteScore($data, $result['clusters'])];
                $this->silhouetteScore = $this->silhouetteScores[2];

                // Simpan hasil
                Session::put('kmeans_optimal_k', $this->optimalK);
                Session::put('kmeans_max_iterations', $this->maxIterations);
                Session::put('kmeans_centroid_type', $this->centroidType);
                Session::put('kmeans_wcss', $this->wcss);
                Session::put('kmeans_silhouette_scores', $this->silhouetteScores);
                return;
            }

            // Untuk data lebih dari 2, cari K optimal
            $minK = 2;
            $maxK = min($numData - 1, 3); // Batasi K maksimal ke 3 untuk data sedikit
            $this->wcss = [];
            $this->silhouetteScores = [];
            $bestSilhouetteScore = -1;
            $bestK = null;

            // Hitung WCSS dan Silhouette Score untuk setiap K
            for ($k = $minK; $k <= $maxK; $k++) {
                try {
                    $result = KMeansHelper::kmeans($data, $k, $this->maxIterations, $this->centroidType);

                    if (!empty($result['clusters']) && !empty($result['centroids'])) {
                        // Hitung WCSS
                        $wcss = KMeansHelper::calculateWCSS($data, $result['clusters'], $result['centroids']);
                        // Hitung Silhouette Score
                        $silhouetteScore = KMeansHelper::calculateSilhouetteScore($data, $result['clusters']);

                        $this->wcss[$k] = $wcss;
                        $this->silhouetteScores[$k] = $silhouetteScore;

                        if ($silhouetteScore > $bestSilhouetteScore) {
                            $bestSilhouetteScore = $silhouetteScore;
                            $bestK = $k;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Jika tidak menemukan K optimal, gunakan K=2
            if ($bestK === null) {
                $bestK = 2;
                $result = KMeansHelper::kmeans($data, $bestK, $this->maxIterations, $this->centroidType);
                $this->wcss[$bestK] = KMeansHelper::calculateWCSS($data, $result['clusters'], $result['centroids']);
                $this->silhouetteScores[$bestK] = KMeansHelper::calculateSilhouetteScore($data, $result['clusters']);
                $bestSilhouetteScore = $this->silhouetteScores[$bestK];
            }

            $this->optimalK = $bestK;
            $this->silhouetteScore = $bestSilhouetteScore;

            // Simpan hasil
            Session::put('kmeans_optimal_k', $this->optimalK);
            Session::put('kmeans_max_iterations', $this->maxIterations);
            Session::put('kmeans_centroid_type', $this->centroidType);
            Session::put('kmeans_wcss', $this->wcss);
            Session::put('kmeans_silhouette_scores', $this->silhouetteScores);
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
            return redirect('/admin/k-means/dataset');
        }
    }

    public function goToDefineCluster()
    {
        if (!$this->optimalK) {
            Session::flash('error', 'Nilai K optimal belum ditentukan.');
            return;
        }

        return redirect('/admin/k-means/define-cluster');
    }
}
