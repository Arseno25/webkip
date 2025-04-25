<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\KipRecipient;
use App\Models\School;
use App\Models\Subdistrict;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class MapReport extends Component implements HasForms
{
    use InteractsWithForms;
    
    public $selectedSubdistrict = null;
    public $selectedSchool = null;
    public $selectedLevel = null;
    public $mapData = [];
    
    public function mount(): void
    {
        $this->form->fill();
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
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->selectedSchool = null; // Reset sekolah saat kecamatan berubah
                        $this->mapData = $this->getMapData();
                    }),
                
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
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->mapData = $this->getMapData();
                    }),
                
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
                    ->afterStateUpdated(function () {
                        $this->mapData = $this->getMapData();
                    }),
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
    
    public function render()
    {
        return view('livewire.map-report');
    }
}
