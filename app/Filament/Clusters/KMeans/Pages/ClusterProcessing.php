<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use App\Helpers\KMeansHelper;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

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

        // Cek apakah data sudah ada di session
        if (!session()->has('kmeans_data')) {
            session()->flash('error', 'Silakan muat data terlebih dahulu.');
            return redirect('/admin/k-means/dataset');
        }

        try {
            // Baca parameter
            $k = session('kmeans_k', 4);
            $maxIterations = session('kmeans_max_iterations', 10);
            $centroidMethod = session('kmeans_type_centroid', 'rata-rata');

            // Ambil data dari session
            $data = session('kmeans_data');
            $header = session('kmeans_header');
            $this->header = $header;

            // Proses K-Means
            $result = KMeansHelper::kmeans($data, $k, $maxIterations, $centroidMethod);

            $this->result = $result;
            $this->iterations = $result['iterations'];
            $this->silhouetteScore = KMeansHelper::calculateSilhouetteScore($data, $result['clusters']);
            $this->rows = collect($data)->map(function ($row, $index) use ($header) {
                return array_combine($header, $row);
            })->toArray();

            // Simpan hasil untuk halaman berikutnya
            session([
                'kmeans_result' => $result,
                'kmeans_header' => $header,
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }
}
