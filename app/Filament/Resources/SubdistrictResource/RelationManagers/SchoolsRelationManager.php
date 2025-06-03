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

class SchoolsRelationManager extends RelationManager
{
    protected static string $relationship = 'schools';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Sekolah';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Sekolah')
                    ->required()
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
                    ->rows(3),
            Map::make('location')
                    ->label('Lokasi Sekolah')
                ->defaultLocation(latitude: -6.2088, longitude: 106.8456)
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Sekolah')
                    ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Jenjang')
                ->sortable(),
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
