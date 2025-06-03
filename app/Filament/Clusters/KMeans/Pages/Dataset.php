<?php

namespace App\Filament\Clusters\KMeans\Pages;

use Filament\Pages\Page;
use App\Filament\Clusters\KMeans;
use App\Models\KipRecipient;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Session;

class Dataset extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.clusters.k-means.pages.dataset';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Dataset';
    protected static ?string $title = 'Dataset K-Means';
    protected static ?int $navigationSort = 1;
    protected static ?string $cluster = KMeans::class;

    public $rawRows = [];
    public $header = [];
    public $isDataLoaded = false;

    public function mount()
    {
        try {
            // Ambil data dari database
            $data = KipRecipient::with(['school', 'subdistrict'])->get();

            if ($data->isEmpty()) {
                Session::flash('error', 'Tidak ada data yang tersedia.');
                return;
            }

            // Set header
            $this->header = ['school', 'subdistrict', 'year_received', 'amount', 'recipient'];

            // Transform data untuk ditampilkan
            $this->rawRows = $data->map(function ($item) {
                return [
                    'school' => $item->school->name,
                    'subdistrict' => $item->subdistrict->name,
                    'year_received' => $item->year_received,
                    'amount' => $item->amount,
                    'recipient' => $item->recipient
                ];
            })->toArray();

            // Transform data untuk clustering
            $clusteringData = $data->map(function ($item) {
                return [
                    'school_id' => $item->school_id,
                    'subdistrict_id' => $item->subdistrict_id,
                    'year_received' => $item->year_received,
                    'amount' => $item->amount,
                    'recipient' => $item->recipient
                ];
            })->toArray();

            // Simpan data ke session
            Session::put('kmeans_data', $clusteringData);

            $this->isDataLoaded = true;
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    private function minMaxNormalize(array $rows, array $header): array
    {
        $numericHeader = array_slice($header, 1);
        $min = [];
        $max = [];
        foreach ($numericHeader as $col) {
            $min[$col] = INF;
            $max[$col] = -INF;
        }
        foreach ($rows as $row) {
            foreach ($numericHeader as $col) {
                $val = floatval($row[$col]);
                if ($val < $min[$col]) $min[$col] = $val;
                if ($val > $max[$col]) $max[$col] = $val;
            }
        }
        $normalized = [];
        foreach ($rows as $row) {
            $normRow = [$header[0] => $row[$header[0]]];
            foreach ($numericHeader as $col) {
                $val = floatval($row[$col]);
                $normRow[$col] = ($max[$col] - $min[$col]) == 0
                    ? 0
                    : round(($val - $min[$col]) / ($max[$col] - $min[$col]), 4);
            }
            $normalized[] = $normRow;
        }
        return $normalized;
    }

    public function goToDefineCluster()
    {
        if (!$this->isDataLoaded) {
            Session::flash('error', 'Data belum tersedia. Silakan tunggu hingga data selesai dimuat.');
            return;
        }
        return redirect('/admin/k-means/define-cluster');
    }
}
