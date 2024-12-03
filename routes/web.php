<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Route;

Route::post('/analytics/update-gemini-analysis/{business}', [AnalyticsController::class, 'updateGeminiAnalysis'])
    ->name('analytics.update-gemini-analysis');

Route::prefix('analytics')->group(function () {
    Route::get('/competitors-analysis/{business}', [AnalyticsController::class, 'analyzeCompetitors']);
    Route::get('/smart-post/{business}', [AutomationController::class, 'createSmartPost']);
});

Route::post('/analytics/{business}/refresh-analysis', [AnalyticsController::class, 'refreshAnalysis'])
    ->name('analytics.refresh-analysis');

    Route::get('/test-gemini', function () {
        $gemini = app(App\Services\GeminiService::class);
        return $gemini->generateContent("Teste");
    })->name('test.gemini');

Route::get('/test-suggestions', function() {
    $gemini = app(App\Services\GeminiService::class);
    return $gemini->generateSuggestions("Uma loja de roupas que precisa aumentar vendas online");
});

Route::get('/test-review', function() {
    $gemini = app(App\Services\GeminiService::class);
    return $gemini->generateReviewResponse("Cliente insatisfeito com o tempo de entrega");
});

Route::get('/test-gemini', function() {
    $gemini = app(App\Services\GeminiService::class);
    return $gemini->generateContent("Crie um post sobre marketing digital");
});



// Rota principal (pública)
Route::get('/', function () {
    return view('welcome');
});

// Rotas do Google
Route::get('/google/auth', [GoogleController::class, 'auth'])->name('google.auth');
Route::get('/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// Todas as rotas autenticadas

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Rotas de Negócios
    Route::resource('business', BusinessController::class);
    Route::get('/business/{business}/automation', [BusinessController::class, 'automation'])
        ->name('business.automation');

    // Rotas de Automação
    Route::prefix('automation')->name('automation.')->group(function () {
        // Rotas do Calendário
        Route::get('/calendar-events', [AutomationController::class, 'getCalendarEvents'])->name('calendar.events');
        Route::post('/calendar-event', [AutomationController::class, 'createCalendarEvent'])->name('calendar.store');
        Route::get('/smart-calendar', [AutomationController::class, 'smartCalendar'])->name('smart-calendar');
        Route::get('/calendar-suggestions', [AutomationController::class, 'getSmartCalendarSuggestions'])->name('calendar-suggestions');
        
        // Outras rotas de automação
        Route::get('/', [AutomationController::class, 'index'])->name('index');
        Route::post('/create-post', [AutomationController::class, 'createPost'])->name('create-post');
        Route::post('/update-hours', [AutomationController::class, 'updateHours'])->name('update-hours');
        Route::post('/holiday-hours', [AutomationController::class, 'updateHolidayHours'])->name('holiday-hours');
        Route::post('/respond-review', [AutomationController::class, 'respondReview'])->name('respond-review');
    });

    // Rotas de Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/{business}', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/{business}/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/{business}/data', [AnalyticsController::class, 'getData'])->name('data');
        Route::get('/{business}/competitors', [AnalyticsController::class, 'competitors'])->name('competitors');
        Route::get('/{business}/performance', [AnalyticsController::class, 'performance'])->name('performance');
        
        // Exportações
        Route::get('/{business}/export/pdf', [AnalyticsController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{business}/export/excel', [AnalyticsController::class, 'exportExcel'])->name('export.excel');
        
        // Relatórios
        Route::get('/{business}/report/{type}', [AnalyticsController::class, 'generateReport'])->name('report');
    });
    
    // Rotas de Metas
    Route::prefix('business/{business}/goals')->name('goals.')->group(function () {
        Route::get('/', [GoalController::class, 'index'])->name('index');
        Route::post('/', [GoalController::class, 'store'])->name('store');
    });
    
    // Rotas de Notificações
    Route::prefix('business/{business}/notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [NotificationController::class, 'updateSettings'])->name('settings.update');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read.all');
    });
    
    // Rotas de Perfil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
        Route::patch('/notifications', [ProfileController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/api-tokens', [ProfileController::class, 'apiTokens'])->name('api-tokens');
    });
});

// Rotas de autenticação
require __DIR__.'/auth.php';

// Rotas de debug (apenas em ambiente local)
if (app()->environment('local')) {
    Route::prefix('debug')->name('debug.')->middleware(['auth'])->group(function () {
        Route::get('/routes', function () {
            $routeCollection = Route::getRoutes();
            return view('debug.routes', ['routes' => $routeCollection]);
        })->name('routes');
        
        Route::get('/clear-cache', function () {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            return "Cache cleared successfully!";
        })->name('clear-cache');
    });
}