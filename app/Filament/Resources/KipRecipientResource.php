<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KipRecipientResource\Pages;
use App\Filament\Resources\KipRecipientResource\RelationManagers;
use App\Models\KipRecipient;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;

class KipRecipientResource extends Resource
{
    protected static ?string $model = KipRecipient::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Penerima KIP';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Penerima KIP';

    protected static ?string $pluralModelLabel = 'Penerima KIP';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Siswa')
                ->schema([
                        Forms\Components\TextInput::make('year_received')
                            ->label('Tahun Penerimaan KIP')
                            ->required()
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(date('Y')),
                Forms\Components\TextInput::make('recipient')
                    ->label('Jumlah Penerimaan')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('amount')
                    ->label('Nominal')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                    ])->columns(2),

                Forms\Components\Section::make('Sekolah dan Lokasi')
                    ->schema([
                        Forms\Components\Select::make('school_id')
                            ->label('Sekolah')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('subdistrict_id')
                            ->label('Kecamatan')
                            ->relationship('subdistrict', 'name')
                            ->searchable()
                            ->preload()
                    ->required(),
                        Map::make('location')
                            ->label('Lokasi Tempat Tinggal')
                            ->columnSpanFull()
                            // Basic Configuration
                            ->defaultLocation(latitude: -6.2088, longitude: 106.8456) // Jakarta as default
                            ->draggable(true)
                            ->clickable(true)
                            ->zoom(15)
                            ->minZoom(5)
                            ->maxZoom(18)
                            ->detectRetina(true)

                            // Marker Configuration
                            ->showMarker(true)
                            ->markerColor("#10b981") // Green color for KIP recipients

                            // Controls
                            ->showFullscreenControl(true)
                            ->showZoomControl(true)

                    // Location Features
                    //                            ->liveLocation(true, true, 5000)
                    //                            ->showMyLocationButton(true)

                    // Extra Customization
                    ->extraStyles([
                                'min-height: 400px',
                                'border-radius: 8px'
                            ])

                            // State Management
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if ($state && isset($state['lat']) && isset($state['lng'])) {
                                    $set('latitude', (float) $state['lat']);
                                    $set('longitude', (float) $state['lng']);
                                }
                            })
                            ->afterStateHydrated(function ($state, $record, Set $set): void {
                                if ($record && $record->latitude && $record->longitude) {
                                    $set('location', [
                                        'lat' => $record->latitude,
                                        'lng' => $record->longitude,
                                    ]);
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('year_received')
                ->label('Tahun Penerimaan')
                ->sortable(),
                Tables\Columns\TextColumn::make('school.name')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subdistrict.name')
                    ->label('Kecamatan')
                    ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('recipient')
                ->label('Jumlah Penerimaan')
                ->sortable(),
            Tables\Columns\TextColumn::make('amount')
                ->label('Nominal')
                ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('Sekolah')
                    ->relationship('school', 'name'),
                Tables\Filters\SelectFilter::make('subdistrict_id')
                    ->label('Kecamatan')
                    ->relationship('subdistrict', 'name'),
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                ]),
                Tables\Filters\SelectFilter::make('year_received')
                    ->label('Tahun Penerimaan')
                ->options(fn() => KipRecipient::query()->distinct()->pluck('year_received', 'year_received')->toArray()),
                Tables\Filters\Filter::make('is_active')
                    ->label('Status Aktif')
                    ->toggle()
                ->query(fn(Builder $query): Builder => $query->where('is_active', true)),
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

    public static function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('viewMap')
                ->label('Lihat Peta')
                ->icon('heroicon-o-map')
                ->url(fn() => route('filament.admin.resources.reports.map')),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKipRecipients::route('/'),
            'create' => Pages\CreateKipRecipient::route('/create'),
            'edit' => Pages\EditKipRecipient::route('/{record}/edit'),
        ];
    }
}
