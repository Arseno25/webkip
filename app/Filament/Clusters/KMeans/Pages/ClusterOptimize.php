<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use App\Helpers\KMeansHelper;
use App\Models\KipRecipient;
use Filament\Pages\Page;

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

        try {
            // Fetch data from KipRecipient
            $recipients = KipRecipient::whereNotNull('school_id')
                ->with(['school', 'subdistrict'])
                ->get();

            if ($recipients->isEmpty()) {
                throw new \Exception('Tidak ada data penerima KIP');
            }

            // Transform data for clustering
            $rows = $recipients->map(function ($recipient) {
                return [
                    'school_id' => $recipient->school_id,
                    'subdistrict_id' => $recipient->subdistrict_id,
                    'year_received' => $recipient->year_received,
                    'amount' => $recipient->amount ?? 0,
                    'recipient' => $recipient->recipient ?? 0,
                ];
            })->toArray();

            // Ambil header
            $header = array_keys($rows[0]);

            // Konversi ke array numerik
            $data = [];
            foreach ($rows as $row) {
                $numericRow = [];
                foreach (array_values($row) as $val) {
                    $numericRow[] = floatval($val);
                }
                $data[] = $numericRow;
            }

            $this->wcss = [];
            $this->silhouetteScores = [];
            // Hitung WCSS untuk K = kMin sampai kMax
            for ($k = $kMin; $k <= $kMax; $k++) {
                $result = KMeansHelper::kmeans($data, $k, $maxIter);
                $this->wcss[$k] = KMeansHelper::calculateWCSS($result['clusters'], $result['centroids']);
                $this->silhouetteScores[$k] = KMeansHelper::calculateSilhouetteScore($data, $result['clusters']);
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
