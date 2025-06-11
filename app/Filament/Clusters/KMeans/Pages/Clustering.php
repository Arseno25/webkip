<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use App\Helpers\KMeansHelper;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

class Clustering extends Page
{
    use WithFileUploads;

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
        try {
            // Validasi data
            if (empty($this->clusterResults)) {
                Session::flash('error', 'Tidak ada hasil clustering untuk diunduh.');
                return;
            }

            // Siapkan nama file
            $filename = 'hasil_clustering_kmeans_' . date('Y-m-d_H-i-s') . '.csv';
            $tempPath = storage_path('app/public/temp/' . $filename);

            // Pastikan direktori temp ada
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0777, true);
            }

            // Buat file handler
            $handle = fopen($tempPath, 'w');

            // Set UTF-8 BOM untuk support karakter khusus di Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Tulis header informasi clustering
            fputcsv($handle, ['Informasi Clustering']);
            fputcsv($handle, ['Jumlah Cluster (K)', $this->k]);
            fputcsv($handle, ['Jumlah Iterasi', $this->iterations]);
            fputcsv($handle, ['WCSS', number_format($this->wcss, 4)]);
            fputcsv($handle, ['Silhouette Score', number_format($this->silhouetteScore, 4)]);
            fputcsv($handle, []); // Baris kosong

            // Tulis informasi centroid
            fputcsv($handle, ['Informasi Centroid']);
            $centroidHeader = array_merge(['Cluster'], array_keys($this->centroids[0]));
            fputcsv($handle, $centroidHeader);
            foreach ($this->centroids as $i => $centroid) {
                $row = array_merge(['Cluster ' . ($i + 1)], array_map(function ($value) {
                    return number_format($value, 4);
                }, array_values($centroid)));
                fputcsv($handle, $row);
            }
            fputcsv($handle, []); // Baris kosong

            // Tulis hasil clustering
            fputcsv($handle, ['Hasil Clustering']);
            fputcsv($handle, ['Cluster', 'Sekolah', 'Kecamatan', 'Tahun', 'Dana', 'Penerima']);

            foreach ($this->clusterResults as $clusterIndex => $cluster) {
                foreach ($cluster as $data) {
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

            // Tutup file handler
            fclose($handle);

            // Return file untuk didownload
            return response()->download($tempPath, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Session::flash('error', 'Gagal mengunduh hasil: ' . $e->getMessage());
            return null;
        }
    }

    public function goToMap()
    {
        if (empty($this->clusterResults)) {
            Session::flash('error', 'Tidak ada hasil clustering untuk ditampilkan pada peta.');
            return;
        }

        return redirect('/admin/k-means/k-means-map');
    }
}
