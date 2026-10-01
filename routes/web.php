<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadImportController;

Route::get('/leads/import', [LeadImportController::class, 'index'])
    ->name('leads.import');

Route::post('/leads/import', [LeadImportController::class, 'import'])
    ->name('leads.import.process');
