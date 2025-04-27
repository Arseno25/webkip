<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use App\Helpers\KMeansHelper;
use Filament\Pages\Page;
use Spatie\SimpleExcel\SimpleExcelReader;

class ClusterProcessing extends Page
{
    protected static ?string $title = 'Proses K-Means Clustering';
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.clusters.k-means.pages.cluster-processing';

    protected static ?string $cluster = KMeans::class;

    public $result = [];
    public $header = [];
    public $iterations = 0;
    public $silhouetteScore = 0;
    public $rows = [];

    public function mount()
    {
        // Cek apakah parameter cluster sudah di-set
        if (!session()->has('kmeans_k')) {
            session()->flash('error', 'Silakan tentukan parameter cluster terlebih dahulu.');
            return redirect('/admin/k-means/define-cluster');
        }

        // Cek apakah file dataset sudah ada
        $filePath = storage_path('app/public/datasets/dataset.xlsx');
        if (!file_exists($filePath)) {
            $filePath = storage_path('app/public/datasets/dataset.csv');
        }
        if (!file_exists($filePath)) {
            session()->flash('error', 'Silakan upload dataset terlebih dahulu.');
            return redirect('/admin/k-means/dataset');
        }

        try {
            // Baca parameter
            $k = session('kmeans_k', 4);
            $maxIterations = session('kmeans_max_iterations', 10);
            $centroidMethod = session('kmeans_type_centroid', 'rata-rata');

            // Baca dataset (Excel/CSV) secara dinamis
            $filePath = storage_path('app/public/datasets/dataset.xlsx');
            if (!file_exists($filePath)) {
                $filePath = storage_path('app/public/datasets/dataset.csv');
            }
            if (!file_exists($filePath)) {
                throw new \Exception('Dataset tidak ditemukan.');
            }

            $rows = SimpleExcelReader::create($filePath)->getRows()->toArray();

            if (count($rows) < 1) {
                throw new \Exception('Dataset kosong atau tidak valid.');
            }

            // Ambil header
            $header = array_keys($rows[0]);
            $this->header = $header;

            // Filter validRows - pastikan jumlah kolom sama dengan header
            $validRows = [];
            foreach ($rows as $row) {
                $values = array_values($row);
                if (count($header) === count($values)) {
                    $validRows[] = $row;
                }
            }

            // Konversi ke array numerik (skip kolom pertama, misal: nama)
            $data = [];
            foreach ($validRows as $row) {
                $numericRow = [];
                foreach (array_values($row) as $i => $val) {
                    if ($i === 0) continue; // skip kolom pertama (nama)
                    $numericRow[] = floatval($val);
                }
                $data[] = $numericRow;
            }

            // Proses K-Means
            $result = KMeansHelper::kmeans($data, $k, $maxIterations, $centroidMethod);

            $this->result = $result;
            $this->iterations = $result['iterations'];
            $this->silhouetteScore = KMeansHelper::calculateSilhouetteScore($data, $result['clusters']);
            $this->rows = $validRows;

            // Simpan hasil untuk halaman berikutnya
            session([
                'kmeans_result' => $result,
                'kmeans_header' => $header,
                'uploaded_names' => array_column($validRows, $header[0]), // ambil nama dari kolom pertama
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }
}
