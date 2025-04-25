<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubdistrictResource\Pages;
use App\Filament\Resources\SubdistrictResource\RelationManagers;
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

class SubdistrictResource extends Resource
{
    protected static ?string $model = Subdistrict::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Kecamatan';

    protected static ?string $modelLabel = 'Kecamatan';

    protected static ?string $pluralModelLabel = 'Kecamatan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kecamatan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kecamatan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Kecamatan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('district')
                            ->label('Kabupaten/Kota')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('province')
                            ->label('Provinsi')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Map::make('location')
                            ->label('Lokasi Kecamatan')
                            ->columnSpanFull()
                            // Basic Configuration
                            ->defaultLocation(latitude: -9.7318891, longitude: 120.0912804) // Jakarta as default
                            ->draggable(true)
                            ->clickable(true) // click to move marker
                            ->zoom(10)
                            ->minZoom(5)
                            ->maxZoom(18)
                            ->detectRetina(true)

                            // Marker Configuration
                            ->showMarker(true)
                            ->markerColor("#3b82f6")

                            // Controls
                            ->showFullscreenControl(true)
                            ->showZoomControl(true)

                            // Location Features
//                            ->liveLocation(true, true, 5000)
//                            ->showMyLocationButton(true)

                            // GeoMan Integration
                            ->geoMan(true)
                            ->geoManEditable(true)
                            ->geoManPosition('topleft')
                            ->drawCircleMarker(false)
                            ->rotateMode(false)
                            ->drawMarker(true)
                            ->drawPolygon(true)
                            ->drawPolyline(false)
                            ->drawCircle(false)
                            ->drawRectangle(false)
                            ->drawText(false)
                            ->dragMode(true)
                            ->cutPolygon(true)
                            ->editPolygon(true)
                            ->deleteLayer(true)
                            ->setColor('#3388ff')
                            ->setFilledColor('#cad9ec')
                            ->snappable(true, 20)

                            // Extra Customization
                            ->extraStyles([
                                'min-height: 500px',
                                'border-radius: 8px'
                            ])

                            // State Management
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if ($state) {
                                    if (isset($state['lat']) && isset($state['lng'])) {
                                        $set('latitude', (float) $state['lat']);
                                        $set('longitude', (float) $state['lng']);
                                    }

                                    if (isset($state['geojson'])) {
                                        $set('boundaries', json_encode($state['geojson']));
                                    }
                                }
                            })
                            ->afterStateHydrated(function ($state, $record, Set $set): void {
                                if ($record) {
                                    $locationData = [
                                        'lat' => $record->latitude,
                                        'lng' => $record->longitude,
                                    ];

                                    if ($record->boundaries) {
                                        $locationData['geojson'] = json_decode($record->boundaries);
                                    }

                                    $set('location', $locationData);
                                }
                            }),

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
                        Forms\Components\Textarea::make('boundaries')
                            ->label('Batas Wilayah (GeoJSON)')
                            ->helperText('Data GeoJSON untuk batas wilayah kecamatan. Anda dapat menggambar langsung pada peta di atas.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kecamatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district')
                    ->label('Kabupaten/Kota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('province')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schools_count')
                    ->label('Jumlah Sekolah')
                    ->counts('schools')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('province')
                    ->label('Provinsi')
                    ->options(fn () => Subdistrict::query()->pluck('province', 'province')->toArray()),
                Tables\Filters\SelectFilter::make('district')
                    ->label('Kabupaten/Kota')
                    ->options(fn () => Subdistrict::query()->pluck('district', 'district')->toArray()),
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
            RelationManagers\SchoolsRelationManager::class,
            RelationManagers\KipRecipientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubdistricts::route('/'),
            'create' => Pages\CreateSubdistrict::route('/create'),
            'edit' => Pages\EditSubdistrict::route('/{record}/edit'),
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
