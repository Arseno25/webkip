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

            // Validasi minimal 2 data
            if ($data->count() < 2) {
                Session::flash('error', 'Minimal harus ada 2 data untuk melakukan clustering.');
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

            // Simpan data mentah untuk referensi
            $rawData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'school_id' => $item->school_id,
                    'school_name' => $item->school->name,
                    'subdistrict_id' => $item->subdistrict_id,
                    'subdistrict_name' => $item->subdistrict->name,
                    'year_received' => $item->year_received,
                    'amount' => $item->amount,
                    'recipient' => $item->recipient
                ];
            })->toArray();

            // Data untuk clustering (hanya fitur numerik)
            $clusteringData = $data->map(function ($item) {
                return [
                    'year_received' => floatval($item->year_received),
                    'amount' => floatval($item->amount),
                    'recipient' => floatval($item->recipient)
                ];
            })->toArray();

            // Normalisasi data
            $normalizedData = $this->normalizeData($clusteringData);

            // Simpan semua data ke session
            Session::put('kmeans_raw_data', $rawData);
            Session::put('kmeans_data', $normalizedData);
            Session::put('kmeans_features', ['year_received', 'amount', 'recipient']);

            $this->isDataLoaded = true;
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }
    }

    private function normalizeData($data)
    {
        if (empty($data)) return [];

        $normalized = [];
        $features = array_keys($data[0]);
        $min = array_fill_keys($features, PHP_FLOAT_MAX);
        $max = array_fill_keys($features, PHP_FLOAT_MIN);

        // Cari nilai min dan max untuk setiap fitur
        foreach ($data as $row) {
            foreach ($features as $feature) {
                $value = $row[$feature];
                $min[$feature] = min($min[$feature], $value);
                $max[$feature] = max($max[$feature], $value);
            }
        }

        // Normalisasi data
        foreach ($data as $row) {
            $normalizedRow = [];
            foreach ($features as $feature) {
                $value = $row[$feature];
                $range = $max[$feature] - $min[$feature];
                $normalizedRow[$feature] = $range == 0 ? 0 : ($value - $min[$feature]) / $range;
            }
            $normalized[] = $normalizedRow;
        }

        // Simpan nilai min dan max untuk denormalisasi nanti
        Session::put('kmeans_min_values', $min);
        Session::put('kmeans_max_values', $max);

        return $normalized;
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    public function goToDefineCluster()
    {
        if (!$this->isDataLoaded) {
            Session::flash('error', 'Data belum tersedia. Silakan tunggu hingga data selesai dimuat.');
            return;
        }

        $data = Session::get('kmeans_data');
        if (empty($data)) {
            Session::flash('error', 'Data clustering tidak tersedia.');
            return;
        }

        if (count($data) < 2) {
            Session::flash('error', 'Minimal harus ada 2 data untuk melakukan clustering.');
            return;
        }

        return redirect('/admin/k-means/cluster-optimize');
    }
}
