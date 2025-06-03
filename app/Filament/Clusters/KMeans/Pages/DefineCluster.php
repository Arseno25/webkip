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

    protected static ?string $title = 'Penentuan Parameter Clustering';
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.clusters.k-means.pages.define-cluster';

    protected static ?string $cluster = KMeans::class;

    public $k = 3;
    public $maxIterations = 100;
    public $centroidType = 'random';

    public function mount()
    {
        // Cek apakah ada data di session
        if (!Session::has('kmeans_data')) {
            Session::flash('error', 'Data belum tersedia. Silakan upload dataset terlebih dahulu.');
            return redirect('/admin/k-means/dataset');
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('centroidType')
                    ->label('Type Centroid')
                    ->options([
                'random' => 'Random',
                'average' => 'Rata-Rata Nilai',
                'first_k' => 'K-Data Pertama'
            ])
                ->required(),
            TextInput::make('k')
                ->label('Jumlah Cluster (K)')
                ->numeric()
                ->minValue(2)
                ->maxValue(10)
                ->required(),
            TextInput::make('maxIterations')
                ->label('Maksimum Iterasi')
                ->numeric()
                ->minValue(1)
                    ->maxValue(1000)
                    ->required(),
            ]);
    }

    public function submit()
    {
        $data = $this->form->getState();

        // Simpan parameter ke session
        Session::put('kmeans_k', $data['k']);
        Session::put('kmeans_max_iterations', $data['maxIterations']);
        Session::put('kmeans_centroid_type', $data['centroidType']);

        // Redirect ke halaman optimasi
        return redirect('/admin/k-means/cluster-optimize');
    }
}
