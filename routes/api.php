<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GooglePlacesController;
use App\Http\Controllers\BusinessImageController;
use App\Http\Controllers\GoogleController;


// Rotas existentes mantidas
Route::get('/business/image', [BusinessImageController::class, 'getImage']);

// Novas rotas para o Google Places API
Route::prefix('places')->group(function () {
    Route::get('/nearby', [GooglePlacesController::class, 'nearbySearch']);
    Route::get('/details', [GooglePlacesController::class, 'placeDetails']);
    Route::get('/autocomplete', [GooglePlacesController::class, 'autocomplete']);
});

// Outras rotas existentes mantidas
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('webhooks/google', [GoogleController::class, 'handleWebhook']);