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

    public function mount(): void
    {
        $this->loadData();
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

    public function loadData()
    {
        try {
            // Fetch all data from KipRecipient
            $recipients = KipRecipient::whereNotNull('school_id')
                ->with(['school', 'subdistrict'])
                ->get();

            if ($recipients->isEmpty()) {
                throw new \Exception('Tidak ada data penerima KIP');
            }

            // Transform data for clustering
            $rows = $recipients->map(function ($recipient) {
                return [
                    'school' => $recipient->school->name ?? 'Tidak ada nama sekolah',
                    'subdistrict' => $recipient->subdistrict->name ?? 'Tidak ada nama kecamatan',
                    'year_received' => $recipient->year_received,
                    'amount' => $recipient->amount ?? 0,
                    'recipient' => $recipient->recipient ?? 0,
                ];
            })->toArray();

            // Ambil header
            $header = array_keys($rows[0]);
            $this->header = $header;
            $this->rawRows = $rows;
            $this->isDataLoaded = true;

            // Konversi ke array numerik untuk clustering
            $data = [];
            foreach ($recipients as $recipient) {
                $numericRow = [];
                $numericRow[] = $recipient->school_id;
                $numericRow[] = $recipient->subdistrict_id;
                $numericRow[] = floatval($recipient->year_received);
                $numericRow[] = floatval($recipient->amount ?? 0);
                $numericRow[] = floatval($recipient->recipient ?? 0);
                $data[] = $numericRow;
            }

            // Simpan data ke session
            Session::put('kmeans_data', $data);
            Session::put('kmeans_header', ['school_id', 'subdistrict_id', 'year_received', 'amount', 'recipient']);

            Notification::make()
                ->title('Data berhasil dimuat')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function lanjutkan()
    {
        if (!$this->isDataLoaded) {
            Notification::make()
                ->title('Data belum berhasil dimuat')
                ->danger()
                ->send();
            return;
        }

        $this->redirect('/admin/k-means/define-cluster');
    }
}
