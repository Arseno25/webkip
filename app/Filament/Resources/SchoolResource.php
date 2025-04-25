<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Filament\Resources\SchoolResource\RelationManagers;
use App\Models\School;
use App\Models\Subdistrict;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Log;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Sekolah';

    protected static ?string $modelLabel = 'Sekolah';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $pluralModelLabel = 'Sekolah';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Sekolah')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Sekolah')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('npsn')
                            ->label('NPSN (Nomor Pokok Sekolah Nasional)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('level')
                            ->label('Jenjang')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'SMK' => 'SMK',
                            ])
                            ->required(),
                        Forms\Components\Select::make('subdistrict_id')
                            ->label('Kecamatan')
                            ->relationship('subdistrict', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak')
                    ->schema([
                        Forms\Components\TextInput::make('principal_name')
                            ->label('Nama Kepala Sekolah')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Map::make('location')
                            ->label('Lokasi Sekolah')
                            ->reactive()
                            ->columnSpanFull()
                            // Basic Configuration
                            ->tilesUrl("https://tile.openstreetmap.org/{z}/{x}/{y}.png")
                            ->defaultLocation(latitude: -9.7318891, longitude: 120.0912804) // Default location
                            ->draggable(true)
                            ->clickable(true)
                            ->zoom(13)
                            ->minZoom(5)
                            ->maxZoom(18)
                            ->detectRetina(true)

                            // Marker Configuration
                            ->showMarker(true)
                            ->markerColor("#e11d48") // Red color for school

                            // Controls
                            ->showFullscreenControl(true)
                            ->showZoomControl(true)

                            // Location Features
                            ->liveLocation(true, true, 5000)
                            ->showMyLocationButton(true)

                            // Extra Customization
                            ->extraStyles([
                                'min-height: 560px',
                                'border-radius: 8px'
                            ])

                            // State Management
                            ->afterStateUpdated(function (Set $set, Get $get, ?array $state): void {
                                if ($state && isset($state['lat']) && isset($state['lng'])) {
                                    // Ambil boundaries dari kecamatan yang dipilih
                                    $set('latitude', (float) $state['lat']);
                                    $set('longitude', (float) $state['lng']);
                                }
                            })
                            ->afterStateHydrated(function ($state, $record, Set $set, Get $get): void {
                                if ($record && $record->latitude && $record->longitude) {
                                    $set('location', [
                                        'lat' => $record->latitude,
                                        'lng' => $record->longitude,
                                    ]);
                                }
                            })
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->maxValue(90)
                            ->minValue(-90)
                            ->readOnly(),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->maxValue(180)
                            ->minValue(-180)
                            ->readOnly(),
                    ]),
            ]);
    }

    public static function getSubdistrictBoundaries($subdistrictId = null)
    {
        if (!$subdistrictId) {
            return null;
        }

        $subdistrict = Subdistrict::find($subdistrictId);

        if ($subdistrict && $subdistrict->boundaries) {
            return $subdistrict->boundaries;
        }

        return null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Sekolah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('npsn')
                    ->label('NPSN')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Jenjang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subdistrict.name')
                    ->label('Kecamatan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('principal_name')
                    ->label('Kepala Sekolah')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('Jenjang')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'SMK' => 'SMK',
                    ]),
                Tables\Filters\SelectFilter::make('subdistrict_id')
                    ->label('Kecamatan')
                    ->relationship('subdistrict', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\KipRecipientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }

    public static function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('viewMap')
                ->label('Lihat Peta')
                ->icon('heroicon-o-map')
                ->url(fn () => route('filament.admin.resources.reports.map')),
        ];
    }
}
