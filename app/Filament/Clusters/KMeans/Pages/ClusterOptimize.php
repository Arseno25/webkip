<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use App\Helpers\KMeansHelper;

use Filament\Pages\Page;
use Spatie\SimpleExcel\SimpleExcelReader;

class ClusterOptimize extends Page
{
    protected static ?string $title = 'Optimasi Metode Euclidean Distance';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.clusters.k-means.pages.cluster-optimize';

    protected static ?string $cluster = KMeans::class;

    public $wcss = [];
    public $silhouetteScores = [];
    public $bestK = null;

    public function mount()
    {
        $kMin = request()->get('k_min', 1);
        $kMax = request()->get('k_max', 10);
        $maxIter = request()->get('max_iter', 100);

        $filePath = storage_path('app/public/datasets/dataset.xlsx');
        if (!file_exists($filePath)) {
            $filePath = storage_path('app/public/datasets/dataset.csv');
        }
        if (!file_exists($filePath)) {
            $this->addError('elbow', 'Dataset tidak ditemukan. Silakan upload dataset terlebih dahulu.');
            return;
        }

        try {
            $rows = \Spatie\SimpleExcel\SimpleExcelReader::create($filePath)->getRows()->toArray();
            if (count($rows) < 1) {
                $this->addError('elbow', 'Dataset kosong atau tidak valid.');
                return;
            }

            // Ambil header
            $header = array_keys($rows[0]);
            // Konversi ke array numerik (skip kolom pertama)
            $data = [];
            foreach ($rows as $row) {
                $numericRow = [];
                foreach (array_values($row) as $i => $val) {
                    if ($i === 0) continue;
                    $numericRow[] = floatval($val);
                }
                $data[] = $numericRow;
            }

            $this->wcss = [];
            $this->silhouetteScores = [];
            // Hitung WCSS untuk K = kMin sampai kMax
            for ($k = $kMin; $k <= $kMax; $k++) {
                $result = \App\Helpers\KMeansHelper::kmeans($data, $k, $maxIter);
                $this->wcss[$k] = \App\Helpers\KMeansHelper::calculateWCSS($result['clusters'], $result['centroids']);
                $this->silhouetteScores[$k] = \App\Helpers\KMeansHelper::calculateSilhouetteScore($data, $result['clusters']);
            }

            $this->bestK = $this->findElbowPoint($this->wcss);
        } catch (\Exception $e) {
            $this->addError('elbow', 'Error: ' . $e->getMessage());
        }
    }

    private function findElbowPoint($wcss)
    {
        $ks = array_keys($wcss);
        $firstK = $ks[0];
        $lastK = $ks[count($ks) - 1];

        $x1 = $firstK;
        $y1 = $wcss[$firstK];
        $x2 = $lastK;
        $y2 = $wcss[$lastK];

        $maxDistance = -1;
        $bestK = $x1;

        foreach ($ks as $k) {
            $x0 = $k;
            $y0 = $wcss[$k];

            // Euclidean distance from point to line
            $numerator = abs(($y2 - $y1) * $x0 - ($x2 - $x1) * $y0 + $x2 * $y1 - $y2 * $x1);
            $denominator = sqrt(pow($y2 - $y1, 2) + pow($x2 - $x1, 2));
            $distance = $denominator == 0 ? 0 : $numerator / $denominator;

            if ($distance > $maxDistance) {
                $maxDistance = $distance;
                $bestK = $k;
            }
        }

        return $bestK;
    }
}
