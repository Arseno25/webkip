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
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MapReport extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static string $resource = ReportResource::class;

    protected static string $view = 'filament.resources.report-resource.pages.map-report';
    
    public $selectedSubdistrict = null;
    public $selectedSchool = null;
    public $selectedLevel = null;
    public $mapData = [];
    
    public function mount(Request $request): void
    {
        // Ambil filter dari URL jika ada
        $this->selectedSubdistrict = $request->query('subdistrict');
        $this->selectedLevel = $request->query('level');
        $this->selectedSchool = $request->query('school');
        
        $this->form->fill([
            'selectedSubdistrict' => $this->selectedSubdistrict,
            'selectedSchool' => $this->selectedSchool,
            'selectedLevel' => $this->selectedLevel,
        ]);
        
        $this->mapData = $this->getMapData();
        
        Log::info('Mount - MapData', [
            'selectedSubdistrict' => $this->selectedSubdistrict,
            'selectedSchool' => $this->selectedSchool,
            'selectedLevel' => $this->selectedLevel,
            'subdistricts' => count($this->mapData['subdistricts']),
            'schools' => count($this->mapData['schools']),
            'kipRecipients' => count($this->mapData['kipRecipients']),
        ]);
    }
    
    public function updatedSelectedSubdistrict()
    {
        $this->selectedSchool = null; // Reset sekolah saat kecamatan berubah
        $this->mapData = $this->getMapData();
    }
    
    public function updatedSelectedSchool()
    {
        $this->mapData = $this->getMapData();
    }
    
    public function updatedSelectedLevel()
    {
        $this->mapData = $this->getMapData();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedSubdistrict')
                    ->label('Kecamatan')
                    ->options(Subdistrict::pluck('name', 'id'))
                    ->placeholder('Semua Kecamatan')
                    ->live(),
                
                Select::make('selectedSchool')
                    ->label('Sekolah')
                    ->options(function () {
                        $query = School::query();
                        
                        if ($this->selectedSubdistrict) {
                            $query->where('subdistrict_id', $this->selectedSubdistrict);
                        }
                        
                        if ($this->selectedLevel) {
                            $query->where('level', $this->selectedLevel);
                        }
                        
                        return $query->pluck('name', 'id');
                    })
                    ->placeholder('Semua Sekolah')
                    ->live(),
                
                Select::make('selectedLevel')
                    ->label('Jenjang')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'SMK' => 'SMK',
                    ])
                    ->placeholder('Semua Jenjang')
                    ->live(),
            ])
            ->columns(3);
    }
    
    public function getMapData(): array
    {
        // Load data untuk subdistrict boundaries
        $subdistrictsQuery = Subdistrict::query()
            ->select(['id', 'name', 'boundaries', 'latitude', 'longitude']);
            
        if ($this->selectedSubdistrict) {
            $subdistrictsQuery->where('id', $this->selectedSubdistrict);
        }
        
        $subdistricts = $subdistrictsQuery->get();
        
        // Load data untuk schools
        $schoolsQuery = School::query()
            ->select(['id', 'name', 'level', 'latitude', 'longitude', 'subdistrict_id']);
            
        if ($this->selectedSubdistrict) {
            $schoolsQuery->where('subdistrict_id', $this->selectedSubdistrict);
        }
        
        if ($this->selectedSchool) {
            $schoolsQuery->where('id', $this->selectedSchool);
        }
        
        if ($this->selectedLevel) {
            $schoolsQuery->where('level', $this->selectedLevel);
        }
        
        $schools = $schoolsQuery->get();
        
        // Load data untuk KIP recipients
        $kipRecipientsQuery = KipRecipient::query()
            ->select(['id', 'name', 'latitude', 'longitude', 'school_id', 'subdistrict_id']);
            
        if ($this->selectedSubdistrict) {
            $kipRecipientsQuery->where('subdistrict_id', $this->selectedSubdistrict);
        }
        
        if ($this->selectedSchool) {
            $kipRecipientsQuery->where('school_id', $this->selectedSchool);
        }
        
        if ($this->selectedLevel) {
            $kipRecipientsQuery->whereHas('school', function ($query) {
                $query->where('level', $this->selectedLevel);
            });
        }
        
        $kipRecipients = $kipRecipientsQuery->get();
        
        // Prepare data untuk peta
        $subdistrictData = $subdistricts->map(function ($subdistrict) {
            return [
                'id' => $subdistrict->id,
                'name' => $subdistrict->name,
                'boundaries' => $subdistrict->boundaries,
                'latitude' => $subdistrict->latitude,
                'longitude' => $subdistrict->longitude,
                'count' => KipRecipient::where('subdistrict_id', $subdistrict->id)->count(),
            ];
        })->toArray();
        
        $schoolData = $schools->map(function ($school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
                'level' => $school->level,
                'latitude' => $school->latitude,
                'longitude' => $school->longitude,
                'count' => KipRecipient::where('school_id', $school->id)->count(),
            ];
        })->toArray();
        
        $recipientData = $kipRecipients->map(function ($kipRecipient) {
            return [
                'id' => $kipRecipient->id,
                'name' => $kipRecipient->name,
                'latitude' => $kipRecipient->latitude,
                'longitude' => $kipRecipient->longitude,
                'school' => $kipRecipient->school ? $kipRecipient->school->name : null,
                'subdistrict' => $kipRecipient->subdistrict ? $kipRecipient->subdistrict->name : null,
            ];
        })->toArray();
        
        return [
            'subdistricts' => $subdistrictData,
            'schools' => $schoolData,
            'kipRecipients' => $recipientData,
        ];
    }
}
