<?php

namespace App\Filament\Clusters\KMeans\Pages;

use App\Filament\Clusters\KMeans;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class DefineCluster extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.clusters.k-means.pages.define-cluster';
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Parameter Cluster';
    protected static ?string $title = 'Parameter Clustering';
    protected static ?int $navigationSort = 3;
    protected static ?string $cluster = KMeans::class;

    public $k;
    public $maxIterations = 100;
    public $centroidType = 'kmeans++';

    public function mount()
    {
        // Ambil nilai K optimal dari session
        $optimalK = Session::get('kmeans_optimal_k');
        if (!$optimalK) {
            Session::flash('error', 'Nilai K optimal belum ditentukan. Silakan lakukan optimasi terlebih dahulu.');
            return redirect('/admin/k-means/cluster-optimize');
        }

        // Set nilai default
        $this->k = $optimalK;
        $this->maxIterations = Session::get('kmeans_max_iterations', 100);
        $this->centroidType = Session::get('kmeans_centroid_type', 'kmeans++');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            TextInput::make('k')
                ->label('Jumlah Cluster (K)')
                ->numeric()
                ->default($this->k)
                ->required()
                ->minValue(2)
                ->maxValue(10)
                ->helperText('Jumlah cluster yang akan dibentuk (2-10). Nilai optimal dari hasil optimasi adalah ' . $this->k),

            TextInput::make('maxIterations')
                ->label('Maksimum Iterasi')
                ->numeric()
                ->default($this->maxIterations)
                ->required()
                ->minValue(50)
                    ->maxValue(1000)
                ->helperText('Jumlah maksimum iterasi untuk proses clustering (50-1000)'),

            Select::make('centroidType')
                ->label('Metode Inisialisasi Centroid')
                ->options([
                    'kmeans++' => 'K-Means++ (Rekomendasi)',
                    'average' => 'Average (Rata-rata)',
                    'random' => 'Random (Acak)'
                ])
                ->default($this->centroidType)
                ->required()
                ->helperText('K-Means++: Inisialisasi cerdas untuk hasil optimal, Average: Berdasarkan rata-rata data, Random: Pemilihan acak')
            ]);
    }

    public function submit()
    {
        $data = $this->form->getState();

        // Validasi input
        if ($data['k'] < 2 || $data['k'] > 10) {
            Session::flash('error', 'Nilai K harus antara 2 dan 10.');
            return;
        }

        if ($data['maxIterations'] < 50 || $data['maxIterations'] > 1000) {
            Session::flash('error', 'Maksimum iterasi harus antara 50 dan 1000.');
            return;
        }

        // Simpan parameter ke session
        Session::put('kmeans_k', $data['k']);
        Session::put('kmeans_max_iterations', $data['maxIterations']);
        Session::put('kmeans_centroid_type', $data['centroidType']);

        return redirect('/admin/k-means/clustering');
    }
}
