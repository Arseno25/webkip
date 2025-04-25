<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\KipRecipient;
use App\Models\School;
use App\Models\Subdistrict;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;

class ReportResource extends Resource
{
    protected static ?string $model = KipRecipient::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Data';

    protected static ?string $modelLabel = 'Laporan';

    protected static ?string $pluralModelLabel = 'Laporan';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form tidak digunakan untuk resource ini
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subdistrict.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('school.name')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('school.level')
                    ->label('Jenjang')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('kip_number')
                    ->label('Nomor KIP')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'Laki-laki' : 'Perempuan')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('grade')
                    ->label('Kelas')
                    ->formatStateUsing(fn (?string $state): string => $state ? "Kelas $state" : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('year_received')
                    ->label('Tahun Penerimaan')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subdistrict_id')
                    ->label('Kecamatan')
                    ->options(fn () => Subdistrict::pluck('name', 'id')->toArray())
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('Sekolah')
                    ->options(fn () => School::pluck('name', 'id')->toArray())
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('school_level')
                    ->label('Jenjang Sekolah')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'SMK' => 'SMK',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $levels): Builder => $query->whereHas(
                                'school',
                                fn (Builder $query): Builder => $query->whereIn('level', $levels)
                            )
                        );
                    }),
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
                Tables\Filters\SelectFilter::make('grade')
                    ->label('Kelas')
                    ->options([
                        '1' => 'Kelas 1',
                        '2' => 'Kelas 2',
                        '3' => 'Kelas 3',
                        '4' => 'Kelas 4',
                        '5' => 'Kelas 5',
                        '6' => 'Kelas 6',
                        '7' => 'Kelas 7',
                        '8' => 'Kelas 8',
                        '9' => 'Kelas 9',
                        '10' => 'Kelas 10',
                        '11' => 'Kelas 11',
                        '12' => 'Kelas 12',
                    ]),
                Tables\Filters\SelectFilter::make('year_received')
                    ->label('Tahun Penerimaan')
                    ->options(fn () => KipRecipient::query()->distinct()->pluck('year_received', 'year_received')->toArray()),
                Tables\Filters\Filter::make('is_active')
                    ->label('Status Aktif')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->actions([
                // Tidak ada action untuk report
            ])
            ->bulkActions([
                // Tidak ada bulk action untuk report
            ])
            ->emptyStateHeading('Tidak ada data penerima KIP')
            ->emptyStateDescription('Data penerima KIP akan muncul di sini setelah Anda menambahkannya.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->poll('30s'); // Auto refresh setiap 30 detik
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
            'index' => Pages\ListReports::route('/'),
            'map' => Pages\MapReport::route('/map'),
            'export' => Pages\ExportReport::route('/export'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\KipStatisticsWidget::class,
            \App\Filament\Widgets\SchoolLevelStatisticsWidget::class,
        ];
    }
}
