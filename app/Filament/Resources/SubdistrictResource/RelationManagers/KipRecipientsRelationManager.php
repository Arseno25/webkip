<?php

namespace App\Filament\Resources\SubdistrictResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Dotswan\MapPicker\Fields\Map;

class KipRecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'kipRecipients';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Penerima KIP';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('school_id')
                    ->label('Sekolah')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload()
                ->required(),
                Forms\Components\TextInput::make('year_received')
                    ->label('Tahun Penerimaan KIP')
                    ->required()
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(date('Y')),
            Forms\Components\TextInput::make('recipient')
                ->label('Jumlah Penerimaan')
                ->required()
                ->numeric()
                ->minValue(0),
            Forms\Components\TextInput::make('amount')
                ->label('Nominal')
                ->required()
                ->numeric()
                ->minValue(0),
            Map::make('location')
                    ->label('Lokasi Tempat Tinggal')
                ->defaultLocation(latitude: -6.2088, longitude: 106.8456) // Jakarta as default
                ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('latitude', $state['lat']);
                            $set('longitude', $state['lng']);
                        }
                    }),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->maxValue(90)
                    ->minValue(-90)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if ($state && $get('longitude')) {
                            $set('location', [
                                'lat' => (float) $state,
                                'lng' => (float) $get('longitude'),
                            ]);
                        }
                    })
                    ->hidden(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->maxValue(180)
                    ->minValue(-180)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if ($state && $get('latitude')) {
                            $set('location', [
                                'lat' => (float) $get('latitude'),
                                'lng' => (float) $state,
                            ]);
                        }
                    })
                    ->hidden(),
            ]);
    }

    public function table(Table $table): Table
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
            Tables\Columns\TextColumn::make('recipient')
                ->label('Jumlah Penerimaan')
                ->sortable(),
            Tables\Columns\TextColumn::make('amount')
                ->label('Nominal')
                ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('Sekolah')
                ->relationship('school', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
