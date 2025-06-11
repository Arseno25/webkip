<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use App\Helpers\KMeansHelper;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class Clustering extends Page
{
    protected static string $view = 'filament.clusters.k-means.pages.clustering';
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Hasil Clustering';
    protected static ?string $title = 'Hasil K-Means Clustering';
    protected static ?int $navigationSort = 4;
    protected static ?string $cluster = KMeans::class;

    public $k;
    public $maxIterations;
    public $centroidType;
    public $clusterResults = [];
    public $centroids = [];
    public $wcss;
    public $silhouetteScore;
    public $iterations;
    public $features;
    public $rawData;

    public function mount()
    {
        try {
            // Ambil parameter clustering dari session
            $this->k = Session::get('kmeans_k');
            $this->maxIterations = Session::get('kmeans_max_iterations');
            $this->centroidType = Session::get('kmeans_centroid_type');
            $this->features = Session::get('kmeans_features');

            if (!$this->k || !$this->maxIterations || !$this->centroidType) {
                Session::flash('error', 'Parameter clustering belum lengkap. Silakan tentukan parameter terlebih dahulu.');
                return redirect('/admin/k-means/define-cluster');
            }

            // Ambil data untuk clustering
            $data = Session::get('kmeans_data');
            $this->rawData = Session::get('kmeans_raw_data');
            if (!$data || !$this->rawData) {
                Session::flash('error', 'Data clustering tidak tersedia. Silakan mulai dari awal.');
                return redirect('/admin/k-means/dataset');
            }

            // Jalankan K-Means clustering
            $result = KMeansHelper::kmeans($data, $this->k, $this->maxIterations, $this->centroidType);

            if (empty($result['clusters']) || empty($result['centroids'])) {
                throw new \Exception('Proses clustering gagal. Silakan coba lagi.');
            }

            // Simpan hasil clustering
            $this->centroids = $this->denormalizeCentroids($result['centroids']);
            $this->iterations = $result['iterations'];

            // Hitung metrik evaluasi
            $this->wcss = KMeansHelper::calculateWCSS($data, $result['clusters'], $result['centroids']);
            $this->silhouetteScore = KMeansHelper::calculateSilhouetteScore($data, $result['clusters']);

            // Kelompokkan data berdasarkan cluster
            $this->clusterResults = $this->groupDataByClusters($result['clusters']);

            // Simpan hasil ke session untuk penggunaan selanjutnya
            Session::put('kmeans_results', [
                'clusters' => $this->clusterResults,
                'centroids' => $this->centroids,
                'wcss' => $this->wcss,
                'silhouette_score' => $this->silhouetteScore,
                'iterations' => $this->iterations
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
            return redirect('/admin/k-means/define-cluster');
        }
    }

    private function denormalizeCentroids($centroids)
    {
        $minValues = Session::get('kmeans_min_values');
        $maxValues = Session::get('kmeans_max_values');

        if (!$minValues || !$maxValues) {
            return $centroids;
        }

        $denormalized = [];
        foreach ($centroids as $centroid) {
            $denormCentroid = [];
            foreach ($centroid as $feature => $value) {
                $range = $maxValues[$feature] - $minValues[$feature];
                $denormCentroid[$feature] = $range == 0 ? $minValues[$feature] :
                    $value * $range + $minValues[$feature];
            }
            $denormalized[] = $denormCentroid;
        }

        return $denormalized;
    }

    private function groupDataByClusters($clusters)
    {
        $groupedData = array_fill(0, $this->k, []);

        foreach ($clusters as $clusterIndex => $pointIndices) {
            foreach ($pointIndices as $index) {
                $groupedData[$clusterIndex][] = $this->rawData[$index];
            }
        }

        return $groupedData;
    }

    public function downloadResults()
    {
        // Implementasi untuk mengunduh hasil clustering dalam format CSV
        $results = Session::get('kmeans_results');
        if (!$results) {
            Session::flash('error', 'Hasil clustering tidak tersedia.');
            return;
        }

        $filename = 'kmeans_clustering_results_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $handle = fopen('php://temp', 'w+');

        // Header
        fputcsv($handle, array_merge(
            ['Cluster', 'School', 'Subdistrict', 'Year Received', 'Amount', 'Recipient']
        ));

        // Data per cluster
        foreach ($results['clusters'] as $clusterIndex => $clusterData) {
            foreach ($clusterData as $data) {
                fputcsv($handle, [
                    'Cluster ' . ($clusterIndex + 1),
                    $data['school_name'],
                    $data['subdistrict_name'],
                    $data['year_received'],
                    $data['amount'],
                    $data['recipient']
                ]);
            }
        }

        // Centroid information
        fputcsv($handle, []); // Empty line
        fputcsv($handle, ['Centroids']);
        fputcsv($handle, array_merge(['Cluster'], array_keys($results['centroids'][0])));
        foreach ($results['centroids'] as $i => $centroid) {
            fputcsv($handle, array_merge(['Cluster ' . ($i + 1)], array_values($centroid)));
        }

        // Evaluation metrics
        fputcsv($handle, []);
        fputcsv($handle, ['Evaluation Metrics']);
        fputcsv($handle, ['WCSS', $results['wcss']]);
        fputcsv($handle, ['Silhouette Score', $results['silhouette_score']]);
        fputcsv($handle, ['Iterations', $results['iterations']]);

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, $headers);
    }
}
