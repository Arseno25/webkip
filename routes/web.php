<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return view('welcome');
});

// Route untuk export data
Route::get('/admin/resources/reports/export', [ExportController::class, 'exportKipRecipients'])
    ->name('filament.admin.resources.reports.export');
