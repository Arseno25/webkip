<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\Page;
use App\Models\KipRecipient;
use App\Models\School;
use App\Models\Subdistrict;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\DB;

class ChartReport extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static string $resource = ReportResource::class;

    protected static string $view = 'filament.resources.report-resource.pages.chart-report';
    
    protected static ?string $title = 'Grafik Distribusi';
    
    public $selectedYear = null;
    public $selectedSubdistrict = null;
    public $selectedLevel = null;
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedYear')
                    ->label('Tahun Penerimaan')
                    ->options(KipRecipient::select('year_received')
                        ->distinct()
                        ->orderBy('year_received')
                        ->pluck('year_received', 'year_received')
                        ->toArray())
                    ->placeholder('Semua Tahun')
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('refresh-chart')),
                
                Select::make('selectedSubdistrict')
                    ->label('Kecamatan')
                    ->options(Subdistrict::pluck('name', 'id'))
                    ->placeholder('Semua Kecamatan')
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('refresh-chart')),
                
                Select::make('selectedLevel')
                    ->label('Jenjang')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'SMK' => 'SMK',
                    ])
                    ->placeholder('Semua Jenjang')
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('refresh-chart')),
            ])
            ->columns(3);
    }
    
    public function getChartData(): array
    {
        // Data untuk grafik distribusi berdasarkan kecamatan
        $subdistrictChartQuery = KipRecipient::query()
            ->select('subdistrict_id', DB::raw('count(*) as total'))
            ->groupBy('subdistrict_id');
            
        if ($this->selectedYear) {
            $subdistrictChartQuery->where('year_received', $this->selectedYear);
        }
        
        if ($this->selectedLevel) {
            $subdistrictChartQuery->whereHas('school', function ($query) {
                $query->where('level', $this->selectedLevel);
            });
        }
        
        $subdistrictData = $subdistrictChartQuery->get()
            ->map(function ($item) {
                $subdistrict = Subdistrict::find($item->subdistrict_id);
                return [
                    'name' => $subdistrict ? $subdistrict->name : 'Tidak Ada Kecamatan',
                    'total' => $item->total,
                ];
            });
            
        // Data untuk grafik distribusi berdasarkan jenjang sekolah
        $levelChartQuery = KipRecipient::query()
            ->join('schools', 'kip_recipients.school_id', '=', 'schools.id')
            ->select('schools.level', DB::raw('count(*) as total'))
            ->groupBy('schools.level');
            
        if ($this->selectedYear) {
            $levelChartQuery->where('kip_recipients.year_received', $this->selectedYear);
        }
        
        if ($this->selectedSubdistrict) {
            $levelChartQuery->where('kip_recipients.subdistrict_id', $this->selectedSubdistrict);
        }
        
        $levelData = $levelChartQuery->get();
        
        // Data untuk grafik distribusi berdasarkan jenis kelamin
        $genderChartQuery = KipRecipient::query()
            ->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender');
            
        if ($this->selectedYear) {
            $genderChartQuery->where('year_received', $this->selectedYear);
        }
        
        if ($this->selectedSubdistrict) {
            $genderChartQuery->where('subdistrict_id', $this->selectedSubdistrict);
        }
        
        if ($this->selectedLevel) {
            $genderChartQuery->whereHas('school', function ($query) {
                $query->where('level', $this->selectedLevel);
            });
        }
        
        $genderData = $genderChartQuery->get()
            ->map(function ($item) {
                return [
                    'name' => $item->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                    'total' => $item->total,
                ];
            });
            
        // Data untuk grafik distribusi berdasarkan tahun penerimaan
        $yearChartQuery = KipRecipient::query()
            ->select('year_received', DB::raw('count(*) as total'))
            ->groupBy('year_received')
            ->orderBy('year_received');
            
        if ($this->selectedSubdistrict) {
            $yearChartQuery->where('subdistrict_id', $this->selectedSubdistrict);
        }
        
        if ($this->selectedLevel) {
            $yearChartQuery->whereHas('school', function ($query) {
                $query->where('level', $this->selectedLevel);
            });
        }
        
        $yearData = $yearChartQuery->get();
        
        // Prepare data untuk grafik
        return [
            'subdistrictChart' => [
                'labels' => $subdistrictData->pluck('name')->toArray(),
                'data' => $subdistrictData->pluck('total')->toArray(),
            ],
            'levelChart' => [
                'labels' => $levelData->pluck('level')->toArray(),
                'data' => $levelData->pluck('total')->toArray(),
            ],
            'genderChart' => [
                'labels' => $genderData->pluck('name')->toArray(),
                'data' => $genderData->pluck('total')->toArray(),
            ],
            'yearChart' => [
                'labels' => $yearData->pluck('year_received')->toArray(),
                'data' => $yearData->pluck('total')->toArray(),
            ],
        ];
    }
}
