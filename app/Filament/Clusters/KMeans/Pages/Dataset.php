<?php

namespace App\Filament\Clusters\KMeans\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Livewire\WithFileUploads;
use App\Filament\Clusters\KMeans;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class Dataset extends Page
{
    use Forms\Concerns\InteractsWithForms;
    use WithFileUploads;

    protected static string $view = 'filament.clusters.k-means.pages.dataset';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Upload Dataset';
    protected static ?string $title = 'Dataset K-Means';
    protected static ?int $navigationSort = 1;
    protected static ?string $cluster = KMeans::class;

    public $dataset;
    public $rawRows = [];
    public $normalizedRows = [];
    public $header = [];
    public $uploadedFilePath = null; // path file temp hasil upload
    public $isUploaded = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\FileUpload::make('dataset')
                ->label('Upload File Excel/CSV')
                ->disk('public')
                ->directory('temp')
                ->preserveFilenames()
                ->acceptedFileTypes([
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                    'application/vnd.ms-excel', // .xls
                    'text/csv',
                    'text/plain',
                ])
                ->maxSize(5120)
                ->required()
                ->helperText('File Excel (.xlsx) atau CSV dengan data nilai siswa. Baris pertama adalah header.'),
        ];
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

    public function submit()
    {
        $this->reset(['rawRows', 'normalizedRows', 'header', 'isUploaded', 'uploadedFilePath']);
        try {
            $data = $this->form->getState();

            if (empty($data['dataset'])) {
                throw new \Exception('File tidak ditemukan');
            }

            $tempPath = Storage::disk('public')->path($data['dataset']);
            $rows = SimpleExcelReader::create($tempPath)->getRows()->toArray();

            if (count($rows) < 1) {
                throw new \Exception('Dataset kosong atau tidak valid');
            }

            $this->header = array_keys($rows[0]);
            $this->rawRows = $rows;
            $this->normalizedRows = $this->minMaxNormalize($rows, $this->header);
            $this->uploadedFilePath = $data['dataset'];
            $this->isUploaded = true;

            session()->flash('success', 'File berhasil diupload dan data berhasil ditampilkan!');
        } catch (\Exception $e) {
            $this->addError('dataset', 'Error: ' . $e->getMessage());
        }
    }

    public function lanjutkan()
    {
        try {
            if (!$this->isUploaded || !$this->uploadedFilePath) {
                throw new \Exception('Silakan upload file terlebih dahulu.');
            }

            // Validasi data numerik (kecuali kolom pertama)
            foreach ($this->rawRows as $rowIndex => $row) {
                foreach ($this->header as $colIndex => $colName) {
                    if ($colIndex === 0) continue;
                    $value = trim($row[$colName]);
                    if ($value === '') continue;
                    if (!is_numeric($value)) {
                        throw new \Exception(
                            "Data tidak valid pada baris " . ($rowIndex + 2) .
                                ", kolom $colName: '$value' bukan angka"
                        );
                    }
                }
            }

            // Pastikan folder datasets ada
            if (!Storage::disk('public')->exists('datasets')) {
                Storage::disk('public')->makeDirectory('datasets');
            }

            $finalPath = 'datasets/dataset.xlsx';
            if (Storage::disk('public')->exists($finalPath)) {
                Storage::disk('public')->delete($finalPath);
            }

            $copied = Storage::disk('public')->copy($this->uploadedFilePath, $finalPath);
            if (!$copied) {
                throw new \Exception("Gagal menyalin file ke datasets/dataset.xlsx");
            }
            Storage::disk('public')->delete($this->uploadedFilePath);

            // Simpan header ke session untuk proses selanjutnya
            session(['uploaded_header' => $this->header]);

            session()->flash('success', 'Dataset berhasil disimpan!');
            $this->redirect('/admin/k-means/define-cluster');
        } catch (\Exception $e) {
            $this->addError('dataset', 'Error: ' . $e->getMessage());
        }
    }
}
