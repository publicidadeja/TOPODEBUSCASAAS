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
use App\Http\Controllers\SettingsController;

Route::post('/competitor-analysis/analyze', [CompetitorAnalysisController::class, 'analyze'])
    ->name('competitor-analysis.analyze')
    ->middleware('auth');

Route::post('/competitive/analyze', [CompetitorAnalysisController::class, 'analyze'])->name('competitive.analyze');

Route::post('/analytics/competitors/analyze', [CompetitorAnalysisController::class, 'analyze'])
    ->name('analytics.competitors.analyze');

Route::post('/analytics/update-gemini-analysis/{business}', 
    [AnalyticsController::class, 'updateGeminiAnalysis'])
    ->name('analytics.update-gemini');

Route::post('/analytics/competitors/{business}/refresh', [AnalyticsController::class, 'refreshCompetitorAnalysis']);

Route::prefix('automation')->group(function () {
    Route::get('/suggestions/{business}', 'AutomationController@getAIAssistantSuggestions');
    Route::get('/segment-trends', 'AutomationController@getSegmentTrends');
    Route::get('/segment-events/{business}', 'AutomationController@getSeasonalEvents');
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('automation')->group(function () {
        Route::get('/', [AutomationController::class, 'index'])->name('automation.index');
        Route::get('/calendar-events', [AutomationController::class, 'getCalendarEvents']);
        Route::post('/create-event', [AutomationController::class, 'createCalendarEvent']);
        Route::post('/toggle/{type}', [AutomationController::class, 'toggleAutomation']);
        Route::get('/ai-suggestions', [AutomationController::class, 'getAIAssistantSuggestions']);
        Route::post('/handle-suggestion', [AutomationController::class, 'handleAISuggestion']);
        Route::get('/competitive-analysis', [AutomationController::class, 'getCompetitiveAnalysis']);
        Route::get('/segment-events/{business}', [AutomationController::class, 'getSmartCalendarSuggestions']);
    });
});

// Rotas para dados simulados do Google Meu Negócio
Route::prefix('business/{business}')->group(function () {
    Route::get('/google-data', [BusinessController::class, 'getGoogleData'])->name('business.google-data');
    Route::get('/insights', [BusinessController::class, 'getInsights'])->name('business.insights');
    Route::get('/metrics', [BusinessController::class, 'getMetrics'])->name('business.metrics');
    Route::get('/automation-data', [BusinessController::class, 'getAutomationData'])->name('business.automation-data');
});

Route::get('/automation/segment-events/{business}', [AutomationController::class, 'getSegmentEvents'])
    ->name('automation.segment-events');


Route::prefix('automation')->middleware(['auth'])->group(function () {
    Route::post('/feedback', [AIFeedbackController::class, 'store'])
         ->name('automation.feedback');
    Route::get('/dashboard', [AIFeedbackController::class, 'dashboard'])
         ->name('automation.dashboard');
    Route::get('/export-feedback', [AIFeedbackController::class, 'export'])
         ->name('automation.export-feedback');
});


Route::prefix('automation')->middleware(['auth'])->group(function () {
    Route::get('/handle-insight/{business}/{notification}', [AutomationController::class, 'handleInsight'])
         ->name('automation.handle-insight');
});


Route::prefix('automation')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AutomationController::class, 'dashboard'])
         ->name('automation.dashboard');
    Route::post('/feedback', [AutomationController::class, 'provideFeedback'])
         ->name('automation.feedback');
    Route::get('/export/{type}', [AutomationController::class, 'exportReport'])
         ->name('automation.export');
});

Route::prefix('automation')->middleware(['auth'])->group(function () {
    Route::get('/insights/{business}', [AutomationController::class, 'getAIInsights'])
         ->name('automation.insights');
});

Route::prefix('automation')->group(function () {
    Route::post('/setup/{business}', [AutomationController::class, 'setupAutomation'])
         ->name('automation.setup');
    Route::post('/toggle/{business}/{feature}', [AutomationController::class, 'toggleAutomation'])
         ->name('automation.toggle');
});


Route::get('/automation/protection/{business}', [AutomationController::class, 'protection'])
    ->name('automation.protection');

Route::middleware(['auth', 'has.business'])->group(function () {
    Route::get('/automation', [AutomationController::class, 'index'])->name('automation.index');
    // ... outras rotas de automação
});


Route::prefix('automation')->middleware(['auth'])->group(function () {
    Route::get('/suggestions/{business}', [AutomationController::class, 'getImprovementSuggestions']);
    Route::post('/apply-improvement/{business}', [AutomationController::class, 'applyImprovement']);
});

Route::prefix('automation')->group(function () {
    Route::get('/suggestions/{business}', [AutomationController::class, 'getImprovementSuggestions'])
         ->name('automation.suggestions');
    Route::post('/apply-improvement/{business}', [AutomationController::class, 'applyImprovement'])
         ->name('automation.apply-improvement');
});

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
        Route::get('/competitive/{business}', [AnalyticsController::class, 'competitors'])
        ->name('competitive');
        Route::get('/{business}', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/{business}/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/{business}/data', [AnalyticsController::class, 'getData'])->name('data');
        Route::get('/{business}/competitors', [AnalyticsController::class, 'competitors'])->name('competitors');
        Route::get('/{business}/performance', [AnalyticsController::class, 'performance'])->name('performance');
        Route::get('/export/pdf/{business}', [AnalyticsController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/export/excel/{business}', [AnalyticsController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/{business}', [AnalyticsController::class, 'exportPdf'])->name('export');
        
        // Exportações
        Route::get('/{business}/export/pdf', [AnalyticsController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{business}/export/excel', [AnalyticsController::class, 'exportExcel'])->name('export.excel');
        
        // Relatórios
        Route::get('/{business}/report/{type}', [AnalyticsController::class, 'generateReport'])->name('report');
    });

    Route::prefix('business')->name('business.')->group(function () {
        // Existing business routes...
        
        // Add this new route for settings
        Route::get('/{business}/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/{business}/settings', [SettingsController::class, 'update'])->name('settings.update');
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