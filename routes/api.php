<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CabinetController;

Route::get('/dashboard', [CabinetController::class, 'index']);
Route::get('/cabinets/{id}', [CabinetController::class, 'show']);
Route::get('/cabinets/{id}/power', [CabinetController::class, 'power']);
Route::post('/commands', [CabinetController::class, 'command']);
