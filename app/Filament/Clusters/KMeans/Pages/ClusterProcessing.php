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
        if (!Session::has('kmeans_k')) {
            session()->flash('error', 'Silakan tentukan parameter cluster terlebih dahulu.');
            $this->redirect('/admin/k-means/define-cluster');
            return;
        }

        // Cek apakah data sudah ada di session
        if (!Session::has('kmeans_data')) {
            session()->flash('error', 'Silakan muat data terlebih dahulu.');
            $this->redirect('/admin/k-means/dataset');
            return;
        }

        try {
            // Baca parameter
            $k = Session::get('kmeans_k');
            $maxIterations = Session::get('kmeans_max_iterations');
            $centroidMethod = Session::get('kmeans_type_centroid');

            // Ambil data dari session
            $data = Session::get('kmeans_data');
            $header = Session::get('kmeans_header');
            $schoolMap = Session::get('school_map', []);
            $subdistrictMap = Session::get('subdistrict_map', []);

            // Set header untuk tampilan
            $this->header = ['school', 'subdistrict', 'year_received', 'amount', 'recipient', 'cluster'];

            // Proses K-Means
            $result = KMeansHelper::kmeans($data, $k, $maxIterations, $centroidMethod);

            // Simpan hasil clustering
            $this->result = $result;
            $this->iterations = $result['iterations'];
            $this->silhouetteScore = KMeansHelper::calculateSilhouetteScore($data, $result['clusters']);

            // Transform hasil untuk tampilan
            $this->rows = collect($data)->map(function ($row, $index) use ($header, $schoolMap, $subdistrictMap, $result) {
                // Pastikan jumlah elemen sama
                if (count($row) !== count($header)) {
                    throw new \Exception('Jumlah kolom data tidak sesuai dengan header');
                }

                // Buat array asosiatif dari data
                $rowAssoc = [];
                foreach ($header as $i => $key) {
                    $rowAssoc[$key] = $row[$i];
                }

                // Ambil ID sekolah dan kecamatan
                $schoolId = $rowAssoc['school_id'];
                $subdistrictId = $rowAssoc['subdistrict_id'];

                // Urutkan kolom sesuai header tampilan
                return [
                    'school' => $schoolMap[$schoolId] ?? 'Tidak ada nama sekolah',
                    'subdistrict' => $subdistrictMap[$subdistrictId] ?? 'Tidak ada nama kecamatan',
                    'year_received' => $rowAssoc['year_received'],
                    'amount' => $rowAssoc['amount'],
                    'recipient' => $rowAssoc['recipient'],
                    'cluster' => $result['clusters'][$index]
                ];
            })->toArray();

            // Simpan hasil ke session untuk halaman berikutnya
            Session::put([
                'kmeans_result' => $result,
                'kmeans_rows' => $this->rows,
                'kmeans_header' => $this->header,
                'kmeans_iterations' => $this->iterations,
                'kmeans_silhouette' => $this->silhouetteScore
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function goToOptimize()
    {
        $this->redirect('/admin/k-means/cluster-optimize');
    }
}
