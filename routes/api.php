<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GooglePlacesController;
use App\Http\Controllers\BusinessImageController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\CompetitorAnalysisController;

Route::middleware('api')->group(function () {
    Route::prefix('competitors')->group(function () {
        Route::post('/analyze-single', [CompetitorAnalysisController::class, 'analyzeSingle']);
        Route::get('/details', [GooglePlacesController::class, 'placeDetails']);
    });
});

Route::prefix('competitors')->group(function () {
    Route::get('/keywords/{name}', [CompetitorAnalysisController::class, 'getKeywords']);
    Route::get('/social-presence/{name}', [CompetitorAnalysisController::class, 'getSocialPresence']);
    Route::get('/search-volume/{name}', [CompetitorAnalysisController::class, 'getSearchVolume']);
    Route::get('/full-analysis/{name}', [CompetitorAnalysisController::class, 'getFullAnalysis']);
});

Route::post('/competitors/analyze-single', [CompetitorAnalysisController::class, 'analyzeSingle']);


Route::post('/competitors/analyze-single', [CompetitorAnalysisController::class, 'analyzeSingle'])
    ->name('api.competitors.analyze-single');


Route::post('/competitors/analyze', [CompetitorAnalysisController::class, 'analyze']);

Route::get('/api/competitors/nearby/{business}', 'CompetitorController@nearby');

Route::get('/places/nearby', [PlacesController::class, 'getNearbyPlaces']);

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