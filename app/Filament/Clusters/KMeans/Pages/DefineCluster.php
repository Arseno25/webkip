<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Session;

class DefineCluster extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $cluster = KMeans::class;
    protected static ?string $navigationLabel = 'Tentukan Cluster';
    protected static ?string $title = 'Penentuan Centroid';
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.clusters.k-means.pages.define-cluster';

    public $typeCentroid = 'rata-rata';
    public $jumlahCluster = 4;
    public $maxPerulangan = 10;

    public function mount(): void
    {
        // Cek apakah data sudah ada di session
        if (!Session::has('kmeans_data')) {
            session()->flash('error', 'Silakan muat data terlebih dahulu.');
            $this->redirect('/admin/k-means/dataset');
            return;
        }

        $this->form->fill([
            'typeCentroid' => session('kmeans_type_centroid', 'rata-rata'),
            'jumlahCluster' => session('kmeans_k', 4),
            'maxPerulangan' => session('kmeans_max_iterations', 10),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('typeCentroid')
                ->label('Type Centroid')
                ->options([
                    'rata-rata' => 'Rata-Rata Nilai',
                    'random' => 'Random',
                    'first-k' => 'K-Data Pertama'
                ])
                ->required()
                ->default('rata-rata'),

            Forms\Components\TextInput::make('jumlahCluster')
                ->label('Jumlah Cluster')
                ->numeric()
                ->minValue(2)
                ->maxValue(10)
                ->required()
                ->default(4),

            Forms\Components\TextInput::make('maxPerulangan')
                ->label('Max Perulangan')
                ->numeric()
                ->minValue(1)
                ->maxValue(100)
                ->required()
                ->default(10)
                ->helperText('Maksimum iterasi untuk proses clustering'),
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();

        // Simpan ke session
        session([
            'kmeans_type_centroid' => $data['typeCentroid'],
            'kmeans_k' => $data['jumlahCluster'],
            'kmeans_max_iterations' => $data['maxPerulangan']
        ]);

        session()->flash('success', 'Parameter clustering berhasil disimpan!');
        $this->redirect('/admin/k-means/cluster-processing');
    }
}
